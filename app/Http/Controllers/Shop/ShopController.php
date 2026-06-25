<?php

namespace App\Http\Controllers\Shop;

use App\Enums\ProductStatus;
use App\Enums\ProductVariantStatus;
use App\Enums\ReviewStatus;
use App\Enums\VendorStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(): View
    {
        return view('shop.index', [
            'products' => $this->products(),
        ]);
    }

    private function products(): LengthAwarePaginator
    {
        return Product::query()
            ->select([
                'id',
                'vendor_id',
                'name',
                'description',
                'price',
                'status',
                'created_at',
            ])
            ->with([
                'vendor:id,store_name,status',
                'variants' => fn ($query) => $query
                    ->select(['id', 'product_id', 'sku', 'price', 'status'])
                    ->where('status', ProductVariantStatus::ACTIVE->value)
                    ->orderBy('id'),
            ])
            ->where('status', ProductStatus::PUBLISHED)
            ->whereHas('vendor', fn ($query) => $query->where('status', VendorStatus::APPROVED->value))
            ->withAvg([
                'reviews as approved_reviews_avg_rating' => fn ($query) => $query->where('status', ReviewStatus::APPROVED->value),
            ], 'rating')
            ->withCount([
                'reviews as approved_reviews_count' => fn ($query) => $query->where('status', ReviewStatus::APPROVED->value),
            ])
            ->latest()
            ->paginate((int) config('cart.shop.per_page', 12))
            ->withQueryString();
    }
}
