<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;

interface OrderItemRepositoryInterface
{
    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function createMany(Order $order, array $rows): Collection;

    public function create(array $data): OrderItem;
}
