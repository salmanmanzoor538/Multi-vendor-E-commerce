<?php

namespace App\Repositories\Contracts;

use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface VendorRepositoryInterface
{
    public function create(array $data): Vendor;

    public function update(Vendor $vendor, array $data): Vendor;

    public function findById(int $id): ?Vendor;

    public function findByOwnerId(int $ownerId): ?Vendor;

    public function paginateForAdmin(?string $status = null, int $perPage = 15): LengthAwarePaginator;
}
