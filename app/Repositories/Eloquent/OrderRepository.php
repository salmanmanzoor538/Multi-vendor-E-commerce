<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Order
    {
        return Order::query()->create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->fill($data)->save();

        return Order::query()->findOrFail($order->id);
    }

    public function existsByOrderNumber(string $orderNumber): bool
    {
        return Order::query()->where('order_number', $orderNumber)->exists();
    }

    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery()
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateForVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery()
            ->where('vendor_id', $vendorId)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForUser(int $userId, int $orderId): ?Order
    {
        return $this->detailQuery()
            ->where('user_id', $userId)
            ->whereKey($orderId)
            ->first();
    }

    public function findForVendor(int $vendorId, int $orderId): ?Order
    {
        return $this->detailQuery()
            ->where('vendor_id', $vendorId)
            ->whereKey($orderId)
            ->first();
    }

    public function findForAdmin(int $orderId): ?Order
    {
        return $this->detailQuery()
            ->whereKey($orderId)
            ->first();
    }

    public function findByCheckoutBatchId(string $checkoutBatchId): Collection
    {
        return Order::query()
            ->where('checkout_batch_id', $checkoutBatchId)
            ->get();
    }

    private function listQuery()
    {
        return Order::query()->with([
            'vendor:id,store_name',
            'user:id,name',
        ]);
    }

    private function detailQuery()
    {
        return Order::query()->with([
            'vendor',
            'user',
            'items.product',
            'items.productVariant',
            'statusHistories.changedBy',
        ]);
    }
}
