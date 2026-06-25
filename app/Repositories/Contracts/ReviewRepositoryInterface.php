<?php

namespace App\Repositories\Contracts;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ReviewRepositoryInterface
{
    public function create(array $data): Review;

    public function update(Review $review, array $data): Review;

    public function findByOrderItemId(int $orderItemId): ?Review;

    /**
     * @param  array<int, int>  $orderItemIds
     */
    public function findForUserOrderItems(int $userId, array $orderItemIds): Collection;

    public function paginateForVendor(int $vendorId, int $perPage = 15, ?string $status = null): LengthAwarePaginator;

    public function paginateForAdmin(int $perPage = 15, ?string $status = null): LengthAwarePaginator;
}
