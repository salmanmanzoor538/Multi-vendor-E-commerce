<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Events\PaymentConfirmed;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Support\PerformanceCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentService
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderManagementService $orderManagementService,
    ) {
    }

    /**
     * @return array{checkout_url: string, session_id: string}
     */
    public function createStripeCheckout(User $user, string $checkoutBatchId): array
    {
        $orders = $this->orderRepository->findByCheckoutBatchId($checkoutBatchId);
        abort_if($orders->isEmpty(), 404, 'Orders not found for checkout batch.');
        abort_unless($orders->every(fn ($order) => (int) $order->user_id === (int) $user->id), 403);

        $amount = (float) $orders->sum('grand_total');
        $currency = strtolower((string) $orders->first()->currency);
        $amountInMinor = (int) round($amount * 100);

        $payment = $this->paymentRepository->findByCheckoutBatchId($checkoutBatchId);
        if ($payment !== null) {
            abort_if($payment->provider !== PaymentProvider::STRIPE, 422, 'Stripe payment is not available for this checkout batch.');
            abort_if($payment->status !== PaymentStatus::PENDING, 422, 'Payment has already been processed.');
            abort_unless((int) $payment->user_id === (int) $user->id, 403);
        }

        $secretKey = (string) config('services.stripe.secret_key');
        abort_if($secretKey === '', 500, 'Stripe secret key is not configured.');

        $successUrl = (string) (config('checkout.stripe.success_url') ?: route('customer.checkout.success', ['checkout_batch_id' => $checkoutBatchId]));
        $cancelUrl = (string) (config('checkout.stripe.cancel_url') ?: route('customer.checkout.cancel', ['checkout_batch_id' => $checkoutBatchId]));

        $payload = [
            'mode' => 'payment',
            'success_url' => $this->appendSessionPlaceholder($successUrl),
            'cancel_url' => $cancelUrl,
            'customer_email' => $user->email,
            'client_reference_id' => $checkoutBatchId,
            'metadata[checkout_batch_id]' => $checkoutBatchId,
            'line_items[0][price_data][currency]' => $currency,
            'line_items[0][price_data][unit_amount]' => (string) $amountInMinor,
            'line_items[0][price_data][product_data][name]' => 'Checkout '.$checkoutBatchId,
            'line_items[0][quantity]' => '1',
            'payment_intent_data[metadata][checkout_batch_id]' => $checkoutBatchId,
        ];

        $response = Http::asForm()
            ->withToken($secretKey)
            ->withHeaders([
                'Idempotency-Key' => 'checkout_batch_'.$checkoutBatchId,
            ])
            ->post('https://api.stripe.com/v1/checkout/sessions', $payload);

        abort_unless($response->successful(), 422, 'Unable to initialize Stripe checkout session.');

        $sessionId = (string) $response->json('id');
        $checkoutUrl = (string) $response->json('url');
        $paymentIntentId = $response->json('payment_intent');

        abort_if($sessionId === '' || $checkoutUrl === '', 422, 'Invalid Stripe checkout session response.');

        if ($payment === null) {
            $payment = $this->paymentRepository->create([
                'checkout_batch_id' => $checkoutBatchId,
                'user_id' => $user->id,
                'provider' => PaymentProvider::STRIPE->value,
                'status' => PaymentStatus::PENDING->value,
                'currency' => strtoupper($currency),
                'amount' => $amount,
                'gateway_reference' => $sessionId,
                'stripe_session_id' => $sessionId,
                'stripe_payment_intent_id' => is_string($paymentIntentId) ? $paymentIntentId : null,
                'meta' => [
                    'source' => 'checkout.session.create',
                ],
            ]);
        } else {
            $this->paymentRepository->update($payment, [
                'gateway_reference' => $sessionId,
                'stripe_session_id' => $sessionId,
                'stripe_payment_intent_id' => is_string($paymentIntentId) ? $paymentIntentId : $payment->stripe_payment_intent_id,
                'amount' => $amount,
                'currency' => strtoupper($currency),
            ]);
        }

        $this->syncOrdersFromPayment($checkoutBatchId, PaymentProvider::STRIPE, PaymentStatus::PENDING, $sessionId);

        Log::info('Stripe checkout session created.', [
            'checkout_batch_id' => $checkoutBatchId,
            'session_id' => $sessionId,
            'user_id' => $user->id,
        ]);

        return [
            'checkout_url' => $checkoutUrl,
            'session_id' => $sessionId,
        ];
    }

    public function createCashOnDelivery(User $user, string $checkoutBatchId): Payment
    {
        $orders = $this->orderRepository->findByCheckoutBatchId($checkoutBatchId);
        abort_if($orders->isEmpty(), 404, 'Orders not found for checkout batch.');
        abort_unless($orders->every(fn ($order) => (int) $order->user_id === (int) $user->id), 403);

        $existing = $this->paymentRepository->findByCheckoutBatchId($checkoutBatchId);
        if ($existing !== null) {
            abort_if($existing->provider !== PaymentProvider::COD, 422, 'Cash on delivery is not available for this checkout batch.');
            return $existing;
        }

        $payment = $this->paymentRepository->create([
            'checkout_batch_id' => $checkoutBatchId,
            'user_id' => $user->id,
            'provider' => PaymentProvider::COD->value,
            'status' => PaymentStatus::PENDING->value,
            'currency' => (string) $orders->first()->currency,
            'amount' => (float) $orders->sum('grand_total'),
            'gateway_reference' => 'COD-'.$checkoutBatchId,
            'meta' => [
                'source' => 'cod.create',
            ],
        ]);

        $this->syncOrdersFromPayment($checkoutBatchId, PaymentProvider::COD, PaymentStatus::PENDING, $payment->gateway_reference);

        Log::info('Cash on delivery payment created.', [
            'checkout_batch_id' => $checkoutBatchId,
            'payment_id' => $payment->id,
            'user_id' => $user->id,
        ]);

        return $payment;
    }

    public function handleStripeWebhook(string $payload, string $signature): void
    {
        $event = $this->verifyAndParseStripeEvent($payload, $signature);
        $eventType = (string) ($event['type'] ?? '');

        if (! in_array($eventType, [
            'checkout.session.completed',
            'checkout.session.expired',
            'payment_intent.payment_failed',
        ], true)) {
            return;
        }

        $eventDataObject = $event['data']['object'] ?? [];
        $eventId = (string) ($event['id'] ?? '');
        $metadata = $eventDataObject['metadata'] ?? [];
        $checkoutBatchId = (string) ($metadata['checkout_batch_id'] ?? $eventDataObject['client_reference_id'] ?? '');

        if ($eventType === 'checkout.session.completed') {
            $sessionId = (string) ($eventDataObject['id'] ?? '');
            $paymentIntentId = isset($eventDataObject['payment_intent']) && is_string($eventDataObject['payment_intent'])
                ? $eventDataObject['payment_intent']
                : null;

            $this->markStripeAsPaid($checkoutBatchId, $eventId, $sessionId, $paymentIntentId);

            return;
        }

        if ($eventType === 'checkout.session.expired') {
            $sessionId = (string) ($eventDataObject['id'] ?? '');
            $this->markStripeAsFailed($checkoutBatchId, $eventId, $sessionId, null, 'Stripe checkout session expired.');

            return;
        }

        $paymentIntentId = (string) ($eventDataObject['id'] ?? '');
        $failureReason = (string) ($eventDataObject['last_payment_error']['message'] ?? 'Stripe payment failed.');
        $this->markStripeAsFailed($checkoutBatchId, $eventId, null, $paymentIntentId, $failureReason);
    }

    public function markCodAsPaid(User $actor, string $checkoutBatchId): Payment
    {
        return DB::transaction(function () use ($actor, $checkoutBatchId): Payment {
            $payment = $this->paymentRepository->findByCheckoutBatchId($checkoutBatchId);
            abort_if($payment === null, 404, 'Payment not found.');
            abort_if($payment->provider !== PaymentProvider::COD, 422, 'Only cash on delivery payments can be settled here.');
            abort_if($payment->status === PaymentStatus::PAID, 422, 'Payment is already settled.');
            abort_if($payment->status === PaymentStatus::FAILED, 422, 'Failed payment cannot be settled.');

            $payment = $this->paymentRepository->update($payment, [
                'status' => PaymentStatus::PAID->value,
                'paid_at' => now(),
                'failure_reason' => null,
                'stripe_event_id' => null,
                'meta' => array_merge($payment->meta ?? [], [
                    'settled_by' => $actor->id,
                    'settled_by_role' => $actor->role?->value,
                ]),
            ]);

            $this->syncOrdersFromPayment(
                $checkoutBatchId,
                PaymentProvider::COD,
                PaymentStatus::PAID,
                $payment->gateway_reference
            );

            event(new PaymentConfirmed($checkoutBatchId, (string) $payment->gateway_reference));

            Log::info('Cash on delivery payment settled.', [
                'checkout_batch_id' => $checkoutBatchId,
                'payment_id' => $payment->id,
                'actor_id' => $actor->id,
            ]);

            return $payment;
        });
    }

    private function markStripeAsPaid(string $checkoutBatchId, string $eventId, ?string $sessionId, ?string $paymentIntentId): void
    {
        DB::transaction(function () use ($checkoutBatchId, $eventId, $sessionId, $paymentIntentId): void {
            $payment = $this->resolveStripePayment($checkoutBatchId, $sessionId, $paymentIntentId);
            if ($payment === null || $payment->provider !== PaymentProvider::STRIPE) {
                return;
            }

            if ($payment->status === PaymentStatus::PAID || $payment->status === PaymentStatus::FAILED) {
                return;
            }

            $payment = $this->paymentRepository->update($payment, [
                'status' => PaymentStatus::PAID->value,
                'stripe_event_id' => $eventId,
                'stripe_session_id' => $sessionId ?? $payment->stripe_session_id,
                'stripe_payment_intent_id' => $paymentIntentId ?? $payment->stripe_payment_intent_id,
                'gateway_reference' => $paymentIntentId ?? $sessionId ?? $payment->gateway_reference,
                'failure_reason' => null,
                'paid_at' => now(),
            ]);

            $paymentReference = (string) ($payment->stripe_payment_intent_id ?? $payment->stripe_session_id ?? $payment->gateway_reference);
            $this->syncOrdersFromPayment($payment->checkout_batch_id, PaymentProvider::STRIPE, PaymentStatus::PAID, $paymentReference);
            event(new PaymentConfirmed($payment->checkout_batch_id, $paymentReference));

            Log::info('Stripe payment confirmed.', [
                'checkout_batch_id' => $payment->checkout_batch_id,
                'payment_id' => $payment->id,
                'stripe_event_id' => $eventId,
            ]);
        });
    }

    private function markStripeAsFailed(
        string $checkoutBatchId,
        string $eventId,
        ?string $sessionId,
        ?string $paymentIntentId,
        string $failureReason
    ): void {
        DB::transaction(function () use ($checkoutBatchId, $eventId, $sessionId, $paymentIntentId, $failureReason): void {
            $payment = $this->resolveStripePayment($checkoutBatchId, $sessionId, $paymentIntentId);
            if ($payment === null || $payment->provider !== PaymentProvider::STRIPE) {
                return;
            }

            if ($payment->status === PaymentStatus::PAID) {
                return;
            }

            if ($payment->status === PaymentStatus::FAILED) {
                return;
            }

            $payment = $this->paymentRepository->update($payment, [
                'status' => PaymentStatus::FAILED->value,
                'stripe_event_id' => $eventId,
                'stripe_session_id' => $sessionId ?? $payment->stripe_session_id,
                'stripe_payment_intent_id' => $paymentIntentId ?? $payment->stripe_payment_intent_id,
                'failure_reason' => $failureReason,
            ]);

            $paymentReference = (string) ($payment->stripe_payment_intent_id ?? $payment->stripe_session_id ?? $payment->gateway_reference);
            $this->syncOrdersFromPayment($payment->checkout_batch_id, PaymentProvider::STRIPE, PaymentStatus::FAILED, $paymentReference);

            Log::warning('Stripe payment failed.', [
                'checkout_batch_id' => $payment->checkout_batch_id,
                'payment_id' => $payment->id,
                'stripe_event_id' => $eventId,
                'reason' => $failureReason,
            ]);
        });
    }

    private function resolveStripePayment(string $checkoutBatchId, ?string $sessionId, ?string $paymentIntentId): ?Payment
    {
        if ($sessionId !== null && $sessionId !== '') {
            $payment = $this->paymentRepository->findByStripeSessionId($sessionId);
            if ($payment !== null) {
                return $payment;
            }
        }

        if ($paymentIntentId !== null && $paymentIntentId !== '') {
            $payment = $this->paymentRepository->findByStripePaymentIntentId($paymentIntentId);
            if ($payment !== null) {
                return $payment;
            }
        }

        if ($checkoutBatchId !== '') {
            return $this->paymentRepository->findByCheckoutBatchId($checkoutBatchId);
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function verifyAndParseStripeEvent(string $payload, string $signature): array
    {
        $webhookSecret = (string) config('services.stripe.webhook_secret');
        if ($webhookSecret === '') {
            throw new HttpException(500, 'Stripe webhook secret is not configured.');
        }

        if ($signature === '') {
            throw new HttpException(400, 'Missing Stripe signature header.');
        }

        $parts = [];
        foreach (explode(',', $signature) as $item) {
            [$key, $value] = array_pad(explode('=', trim($item), 2), 2, null);
            if ($key !== null && $value !== null) {
                $parts[$key] = $value;
            }
        }

        $timestamp = isset($parts['t']) ? (int) $parts['t'] : 0;
        $signatureV1 = $parts['v1'] ?? '';
        $tolerance = (int) config('checkout.stripe.webhook_tolerance_seconds', 300);

        if ($timestamp <= 0 || $signatureV1 === '') {
            throw new HttpException(400, 'Invalid Stripe signature header.');
        }

        if (abs(time() - $timestamp) > $tolerance) {
            throw new HttpException(400, 'Stripe signature timestamp is outside the allowed tolerance.');
        }

        $signedPayload = $timestamp.'.'.$payload;
        $expected = hash_hmac('sha256', $signedPayload, $webhookSecret);
        if (! hash_equals($expected, $signatureV1)) {
            throw new HttpException(400, 'Stripe signature verification failed.');
        }

        $decoded = json_decode($payload, true);
        if (! is_array($decoded)) {
            throw new HttpException(400, 'Invalid Stripe payload.');
        }

        return $decoded;
    }

    private function syncOrdersFromPayment(
        string $checkoutBatchId,
        PaymentProvider $provider,
        PaymentStatus $paymentStatus,
        ?string $paymentReference,
    ): void {
        $orders = $this->orderRepository->findByCheckoutBatchId($checkoutBatchId);
        foreach ($orders as $order) {
            $status = $order->status;
            if ($paymentStatus === PaymentStatus::PAID && $order->status === OrderStatus::PENDING) {
                $status = OrderStatus::CONFIRMED;
            } elseif (
                $paymentStatus === PaymentStatus::FAILED
                && in_array($order->status, [OrderStatus::PENDING, OrderStatus::CONFIRMED], true)
            ) {
                $status = OrderStatus::CANCELLED;
            }

            $order = $this->orderRepository->update($order, [
                'payment_provider' => $provider->value,
                'payment_reference' => $paymentReference,
                'payment_status' => $paymentStatus->value,
            ]);

            if ($status !== $order->status) {
                $note = $paymentStatus === PaymentStatus::PAID
                    ? 'Payment confirmed.'
                    : 'Payment failed.';

                $this->orderManagementService->updateStatusFromSystem($order, $status, $note);
            }
        }

        PerformanceCache::invalidate((string) config('performance.cache.namespaces.analytics', 'analytics'));
    }

    private function appendSessionPlaceholder(string $url): string
    {
        if (str_contains($url, '{CHECKOUT_SESSION_ID}')) {
            return $url;
        }

        return str_contains($url, '?')
            ? $url.'&session_id={CHECKOUT_SESSION_ID}'
            : $url.'?session_id={CHECKOUT_SESSION_ID}';
    }
}
