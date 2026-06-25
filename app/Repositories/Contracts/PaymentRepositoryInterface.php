<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;

interface PaymentRepositoryInterface
{
    public function create(array $data): Payment;

    public function findByCheckoutBatchId(string $checkoutBatchId): ?Payment;

    public function findByStripeSessionId(string $sessionId): ?Payment;

    public function findByStripePaymentIntentId(string $paymentIntentId): ?Payment;

    public function update(Payment $payment, array $data): Payment;
}
