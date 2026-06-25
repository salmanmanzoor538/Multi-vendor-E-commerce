<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;

class CartPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasRole(UserRole::CUSTOMER);
    }

    public function manageItem(User $user, CartItem $item): bool
    {
        return $user->hasRole(UserRole::CUSTOMER)
            && $item->cart !== null
            && (int) $item->cart->user_id === (int) $user->id;
    }
}
