<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'checkout_batch_id',
    'order_number',
    'user_id',
    'vendor_id',
    'payment_provider',
    'payment_reference',
    'payment_status',
    'status',
    'currency',
    'subtotal',
    'shipping_amount',
    'grand_total',
    'shipping_address_snapshot',
    'billing_address_snapshot',
])]
class Order extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'shipping_address_snapshot' => 'array',
            'billing_address_snapshot' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'checkout_batch_id', 'checkout_batch_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }
}
