<?php

namespace App\Repositories\Eloquent;

use App\Models\OrderStatusHistory;
use App\Repositories\Contracts\OrderStatusHistoryRepositoryInterface;

class OrderStatusHistoryRepository implements OrderStatusHistoryRepositoryInterface
{
    public function create(array $data): OrderStatusHistory
    {
        return OrderStatusHistory::query()->create($data);
    }
}
