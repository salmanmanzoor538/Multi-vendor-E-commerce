<?php

namespace App\Events;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductVariantsSynced
{
    use Dispatchable, SerializesModels;

    /**
     * @param array<int, ProductVariant> $variants
     */
    public function __construct(
        public Product $product,
        public array $variants,
    ) {
    }
}
