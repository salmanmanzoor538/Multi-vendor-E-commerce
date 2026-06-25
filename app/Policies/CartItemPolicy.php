<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CartItem;
use App\Models\User;

class CartItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::CUSTOMER);
    }

    public function view(User $user, CartItem $cartItem): bool
    {
        return $user->hasRole(UserRole::CUSTOMER) && (int) $cartItem->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, CartItem $cartItem): bool
    {
        return $this->view($user, $cartItem);
    }

    public function delete(User $user, CartItem $cartItem): bool
    {
        return $this->view($user, $cartItem);
    }
}
