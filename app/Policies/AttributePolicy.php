<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Attribute;
use App\Models\User;

class AttributePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN) || $user->hasApprovedVendorAccess();
    }

    public function view(User $user, Attribute $attribute): bool
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        return $attribute->vendor_id === $user->vendor_id || $attribute->vendor_id === $user->ownedVendor?->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN) || $user->hasApprovedVendorAccess();
    }

    public function update(User $user, Attribute $attribute): bool
    {
        return $this->view($user, $attribute);
    }

    public function delete(User $user, Attribute $attribute): bool
    {
        return $this->view($user, $attribute);
    }
}
