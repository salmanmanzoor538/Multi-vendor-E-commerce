<?php

namespace App\Repositories\Eloquent;

use App\Models\Vendor;
use App\Repositories\Contracts\VendorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VendorRepository implements VendorRepositoryInterface
{
    public function create(array $data): Vendor
    {
        return Vendor::query()->create($data);
    }

    public function update(Vendor $vendor, array $data): Vendor
    {
        $vendor->fill($data)->save();

        return $vendor->fresh();
    }

    public function findById(int $id): ?Vendor
    {
        return Vendor::query()
            ->with(['owner', 'approvedBy'])
            ->find($id);
    }

    public function findByOwnerId(int $ownerId): ?Vendor
    {
        return Vendor::query()
            ->where('user_id', $ownerId)
            ->first();
    }

    public function paginateForAdmin(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return Vendor::query()
            ->with(['owner', 'approvedBy'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
