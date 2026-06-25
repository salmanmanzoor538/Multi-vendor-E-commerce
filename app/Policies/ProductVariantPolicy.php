<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

class ProductVariantPolicy
{
    public function generate(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN) || $user->hasApprovedVendorAccess();
    }

    public function sync(User $user, Product $product): bool
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        $vendorId = $user->ownedVendor?->id ?? $user->vendor_id;

        return $user->hasApprovedVendorAccess() && (int) $product->vendor_id === (int) $vendorId;
    }

    public function view(User $user, ProductVariant $productVariant): bool
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        $vendorId = $user->ownedVendor?->id ?? $user->vendor_id;

        return (int) $productVariant->vendor_id === (int) $vendorId;
    }
}
