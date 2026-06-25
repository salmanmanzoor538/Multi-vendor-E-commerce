<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    public function createMany(Order $order, array $rows): Collection
    {
        $created = collect();

        foreach ($rows as $row) {
            $created->push($this->create(array_merge($row, ['order_id' => $order->id])));
        }

        return $created;
    }

    public function create(array $data): OrderItem
    {
        return OrderItem::query()->create($data);
    }
}
