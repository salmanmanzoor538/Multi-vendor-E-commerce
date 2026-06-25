<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->fill($data)->save();

        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }

    public function paginateForVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->where('vendor_id', $vendorId)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Product::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
