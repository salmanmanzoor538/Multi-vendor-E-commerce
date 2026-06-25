<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Events\OrdersPlaced;
use App\Events\PaymentConfirmed;
use Illuminate\Support\Facades\Log;

class LogOrderActivity
{
    public function handle(OrdersPlaced|PaymentConfirmed|OrderStatusUpdated $event): void
    {
        if ($event instanceof OrdersPlaced) {
            Log::info('Orders placed.', [
                'checkout_batch_id' => $event->checkoutBatchId,
                'order_ids' => $event->orders->pluck('id')->all(),
            ]);

            return;
        }

        if ($event instanceof OrderStatusUpdated) {
            Log::info('Order status updated.', [
                'order_id' => $event->order->id,
                'order_number' => $event->order->order_number,
                'from_status' => $event->fromStatus->value,
                'to_status' => $event->toStatus->value,
                'changed_by' => $event->changedBy?->id,
                'note' => $event->note,
            ]);

            return;
        }

        Log::info('Payment confirmed for checkout batch.', [
            'checkout_batch_id' => $event->checkoutBatchId,
            'payment_reference' => $event->paymentReference,
        ]);
    }
}
