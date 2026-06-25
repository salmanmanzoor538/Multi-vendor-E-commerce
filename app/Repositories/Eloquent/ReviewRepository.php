<?php

namespace App\Repositories\Eloquent;

use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function create(array $data): Review
    {
        return Review::query()->create($data);
    }

    public function update(Review $review, array $data): Review
    {
        $review->fill($data)->save();

        return $this->baseQuery()->whereKey($review->id)->firstOrFail();
    }

    public function findByOrderItemId(int $orderItemId): ?Review
    {
        return $this->baseQuery()
            ->where('order_item_id', $orderItemId)
            ->first();
    }

    /**
     * @param  array<int, int>  $orderItemIds
     */
    public function findForUserOrderItems(int $userId, array $orderItemIds): Collection
    {
        if ($orderItemIds === []) {
            return new Collection();
        }

        return $this->baseQuery()
            ->where('user_id', $userId)
            ->whereIn('order_item_id', $orderItemIds)
            ->get();
    }

    public function paginateForVendor(int $vendorId, int $perPage = 15, ?string $status = null): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->where('vendor_id', $vendorId)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateForAdmin(int $perPage = 15, ?string $status = null): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    private function baseQuery()
    {
        return Review::query()->with([
            'vendor',
            'product',
            'user',
            'orderItem.order',
            'moderator',
        ]);
    }
}
