<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::VENDOR) && $user->vendor_id !== null;
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function renew(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function cancel(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $this->viewAny($user) && (int) $subscription->vendor_id === (int) $user->vendor_id;
    }
}
