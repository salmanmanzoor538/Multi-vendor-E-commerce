<?php

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function create(array $data): Payment
    {
        return Payment::query()->create($data);
    }

    public function findByCheckoutBatchId(string $checkoutBatchId): ?Payment
    {
        return Payment::query()
            ->where('checkout_batch_id', $checkoutBatchId)
            ->first();
    }

    public function findByStripeSessionId(string $sessionId): ?Payment
    {
        return Payment::query()
            ->where('stripe_session_id', $sessionId)
            ->first();
    }

    public function findByStripePaymentIntentId(string $paymentIntentId): ?Payment
    {
        return Payment::query()
            ->where('stripe_payment_intent_id', $paymentIntentId)
            ->first();
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->fill($data)->save();

        return $payment->fresh();
    }
}
