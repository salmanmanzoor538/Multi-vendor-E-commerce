<?php

namespace Tests\Unit\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\PaymentRepository;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_late_failed_event_is_ignored_after_payment_is_paid(): void
    {
        config([
            'services.stripe.webhook_secret' => 'whsec_unit',
            'checkout.stripe.webhook_tolerance_seconds' => 300,
        ]);

        [$payment] = $this->seedStripePaymentAndOrder(PaymentStatus::PAID);
        $originalUpdatedAt = $payment->updated_at;

        $payload = json_encode([
            'id' => 'evt_late_failed',
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_unit_1',
                    'metadata' => [
                        'checkout_batch_id' => $payment->checkout_batch_id,
                    ],
                    'last_payment_error' => [
                        'message' => 'Late failure',
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $signature = $this->signPayload($payload, 'whsec_unit');
        $service = new PaymentService(new PaymentRepository, new OrderRepository);
        $service->handleStripeWebhook($payload, $signature);

        $payment->refresh();
        $this->assertSame(PaymentStatus::PAID, $payment->status);
        $this->assertSame($originalUpdatedAt?->toDateTimeString(), $payment->updated_at?->toDateTimeString());
        $this->assertDatabaseHas('orders', [
            'checkout_batch_id' => $payment->checkout_batch_id,
            'payment_status' => PaymentStatus::PAID->value,
            'status' => OrderStatus::CONFIRMED->value,
        ]);
    }

    public function test_late_completed_event_is_ignored_after_payment_is_failed(): void
    {
        config([
            'services.stripe.webhook_secret' => 'whsec_unit',
            'checkout.stripe.webhook_tolerance_seconds' => 300,
        ]);

        [$payment] = $this->seedStripePaymentAndOrder(PaymentStatus::FAILED);
        $existingReference = $payment->gateway_reference;

        $payload = json_encode([
            'id' => 'evt_late_completed',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_unit_1',
                    'payment_intent' => 'pi_unit_1',
                    'metadata' => [
                        'checkout_batch_id' => $payment->checkout_batch_id,
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $signature = $this->signPayload($payload, 'whsec_unit');
        $service = new PaymentService(new PaymentRepository, new OrderRepository);
        $service->handleStripeWebhook($payload, $signature);

        $payment->refresh();
        $this->assertSame(PaymentStatus::FAILED, $payment->status);
        $this->assertSame($existingReference, $payment->gateway_reference);
    }

    /**
     * @return array{0:Payment}
     */
    private function seedStripePaymentAndOrder(PaymentStatus $status): array
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $batchId = (string) fake()->uuid();

        Order::query()->create([
            'checkout_batch_id' => $batchId,
            'order_number' => 'ORD-'.strtoupper(fake()->bothify('########')),
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'payment_provider' => 'stripe',
            'payment_reference' => 'pi_unit_1',
            'payment_status' => $status->value,
            'status' => $status === PaymentStatus::PAID ? OrderStatus::CONFIRMED->value : OrderStatus::CANCELLED->value,
            'currency' => 'USD',
            'subtotal' => 10,
            'shipping_amount' => 2,
            'grand_total' => 12,
            'shipping_address_snapshot' => ['line_1' => 'A'],
            'billing_address_snapshot' => ['line_1' => 'B'],
        ]);

        $payment = Payment::query()->create([
            'checkout_batch_id' => $batchId,
            'user_id' => $user->id,
            'provider' => 'stripe',
            'status' => $status->value,
            'currency' => 'USD',
            'amount' => 12,
            'gateway_reference' => 'pi_unit_1',
            'stripe_session_id' => 'cs_unit_1',
            'stripe_payment_intent_id' => 'pi_unit_1',
        ]);

        return [$payment];
    }

    private function signPayload(string $payload, string $secret): string
    {
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return "t={$timestamp},v1={$signature}";
    }
}

