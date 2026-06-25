<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::ADMIN, UserRole::CUSTOMER, UserRole::VENDOR]);
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        if ($user->hasRole(UserRole::CUSTOMER)) {
            return (int) $order->user_id === (int) $user->id;
        }

        if ($user->hasRole(UserRole::VENDOR)) {
            return (int) $order->vendor_id === (int) $user->vendor_id;
        }

        return false;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        if ($user->hasRole(UserRole::VENDOR)) {
            return (int) $order->vendor_id === (int) $user->vendor_id;
        }

        return false;
    }
}
