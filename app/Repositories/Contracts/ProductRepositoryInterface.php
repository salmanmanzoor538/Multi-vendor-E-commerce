<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool;

    public function paginateForVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator;

    public function slugExists(string $slug, ?int $ignoreId = null): bool;
}
