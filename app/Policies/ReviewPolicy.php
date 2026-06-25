<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::ADMIN, UserRole::VENDOR]);
    }

    public function view(User $user, Review $review): bool
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        if ($user->hasRole(UserRole::VENDOR)) {
            $vendorId = (int) ($user->ownedVendor?->id ?? $user->vendor_id);

            return (int) $review->vendor_id === $vendorId;
        }

        if ($user->hasRole(UserRole::CUSTOMER)) {
            return (int) $review->user_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::CUSTOMER);
    }

    public function moderate(User $user, Review $review): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function reply(User $user, Review $review): bool
    {
        $vendorId = (int) ($user->ownedVendor?->id ?? $user->vendor_id);

        return $user->hasRole(UserRole::VENDOR) && (int) $review->vendor_id === $vendorId;
    }
}
