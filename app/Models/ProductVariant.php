<?php

namespace App\Models;

use App\Enums\ProductVariantStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'product_id',
    'vendor_id',
    'sku',
    'price',
    'stock_qty',
    'status',
    'combination_key',
    'option_snapshot',
])]
class ProductVariant extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_qty' => 'integer',
            'status' => ProductVariantStatus::class,
            'option_snapshot' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductVariantValue::class)->with(['attribute', 'attributeValue']);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
