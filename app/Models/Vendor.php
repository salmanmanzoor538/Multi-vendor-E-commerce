<?php

namespace App\Models;

use App\Enums\VendorStatus;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'store_name',
    'store_slug',
    'status',
    'business_email',
    'business_phone',
    'description',
    'address',
    'approved_at',
    'approved_by',
    'rejection_reason',
])]
class Vendor extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => VendorStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'vendor_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class, 'vendor_id');
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'vendor_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->latestOfMany('ends_at');
    }

    public function isApproved(): bool
    {
        return $this->status === VendorStatus::APPROVED;
    }
}
