<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Enums\VendorStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'vendor_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function hasRole(UserRole|string $role): bool
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        return $this->role?->value === $roleValue;
    }

    /**
     * @param array<int, UserRole|string> $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function dashboardRouteName(): string
    {
        if ($this->role === UserRole::VENDOR && ! $this->hasApprovedVendorAccess()) {
            return 'vendor.register.create';
        }

        if ($this->role === UserRole::VENDOR && ! $this->hasActiveVendorSubscription()) {
            return 'vendor.subscription.index';
        }

        return match ($this->role) {
            UserRole::ADMIN => 'admin.dashboard',
            UserRole::VENDOR => 'vendor.dashboard',
            default => 'customer.dashboard',
        };
    }

    public function ownedVendor(): HasOne
    {
        return $this->hasOne(Vendor::class, 'user_id');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_id', 'vendor_id');
    }

    public function createdCategories(): HasMany
    {
        return $this->hasMany(Category::class, 'created_by');
    }

    public function customerAddresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function moderatedReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'moderated_by');
    }

    public function hasApprovedVendorAccess(): bool
    {
        if ($this->ownedVendor?->status === VendorStatus::APPROVED) {
            return true;
        }

        return $this->vendor?->status === VendorStatus::APPROVED;
    }

    public function hasActiveVendorSubscription(): bool
    {
        $vendor = $this->ownedVendor ?? $this->vendor;
        $subscription = $vendor?->activeSubscription;

        return $subscription !== null
            && $subscription->ends_at !== null
            && $subscription->ends_at->isFuture();
    }
}
