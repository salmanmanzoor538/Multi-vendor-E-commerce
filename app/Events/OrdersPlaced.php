<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OrdersPlaced
{
    use Dispatchable, SerializesModels;

    /**
     * @param Collection<int, \App\Models\Order> $orders
     */
    public function __construct(
        public readonly string $checkoutBatchId,
        public readonly Collection $orders,
    ) {
    }
}
