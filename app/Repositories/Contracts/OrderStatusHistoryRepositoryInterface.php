<?php

namespace App\Repositories\Contracts;

use App\Models\OrderStatusHistory;

interface OrderStatusHistoryRepositoryInterface
{
    public function create(array $data): OrderStatusHistory;
}
