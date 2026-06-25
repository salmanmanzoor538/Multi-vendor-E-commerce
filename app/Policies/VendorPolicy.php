<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function view(User $user, Vendor $vendor): bool
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        return $vendor->user_id === $user->id || $vendor->id === $user->vendor_id;
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $vendor->user_id === $user->id;
    }

    public function approve(User $user, Vendor $vendor): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function reject(User $user, Vendor $vendor): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }
}
