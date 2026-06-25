<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;

    public function update(Order $order, array $data): Order;

    public function existsByOrderNumber(string $orderNumber): bool;

    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function paginateForVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator;

    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator;

    public function findForUser(int $userId, int $orderId): ?Order;

    public function findForVendor(int $vendorId, int $orderId): ?Order;

    public function findForAdmin(int $orderId): ?Order;

    public function findByCheckoutBatchId(string $checkoutBatchId): Collection;
}
