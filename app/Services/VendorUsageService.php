<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;

class VendorUsageService
{
    public function getProductCount(int $vendorId): int
    {
        return Product::query()->where('vendor_id', $vendorId)->count();
    }

    public function getStorageBytes(int $vendorId): int
    {
        return (int) ProductImage::query()
            ->join('products', 'products.id', '=', 'product_images.product_id')
            ->where('products.vendor_id', $vendorId)
            ->sum('product_images.size_bytes');
    }
}
