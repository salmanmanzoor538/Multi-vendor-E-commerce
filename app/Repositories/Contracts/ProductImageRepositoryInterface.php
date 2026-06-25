<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Collection;

interface ProductImageRepositoryInterface
{
    public function createMany(Product $product, array $rows): Collection;

    public function deleteByIds(Product $product, array $imageIds): void;

    public function clearPrimary(Product $product): void;

    public function setPrimary(ProductImage $image): void;

    public function deleteAllForProduct(Product $product): void;
}
