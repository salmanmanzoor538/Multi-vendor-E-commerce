<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use Illuminate\Support\Collection;

class ProductImageRepository implements ProductImageRepositoryInterface
{
    public function createMany(Product $product, array $rows): Collection
    {
        $images = collect();

        foreach ($rows as $row) {
            $images->push($product->images()->create($row));
        }

        return $images;
    }

    public function deleteByIds(Product $product, array $imageIds): void
    {
        if ($imageIds === []) {
            return;
        }

        $product->images()->whereIn('id', $imageIds)->delete();
    }

    public function clearPrimary(Product $product): void
    {
        $product->images()->update(['is_primary' => false]);
    }

    public function setPrimary(ProductImage $image): void
    {
        $image->update(['is_primary' => true]);
    }

    public function deleteAllForProduct(Product $product): void
    {
        $product->images()->delete();
    }
}
