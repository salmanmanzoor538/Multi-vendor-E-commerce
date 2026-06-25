<?php

namespace App\Listeners;

use App\Events\ProductVariantsSynced;
use Illuminate\Support\Facades\Log;

class LogProductVariantActivity
{
    public function handle(ProductVariantsSynced $event): void
    {
        Log::info('Product variants synced', [
            'product_id' => $event->product->id,
            'vendor_id' => $event->product->vendor_id,
            'variant_count' => count($event->variants),
        ]);
    }
}
