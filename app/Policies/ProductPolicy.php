<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::VENDOR) && $user->vendor_id !== null;
    }

    public function view(User $user, Product $product): bool
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        return $user->hasRole(UserRole::VENDOR)
            && $user->vendor_id !== null
            && $product->vendor_id === $user->vendor_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::VENDOR) && $user->vendor_id !== null;
    }

    public function generate(User $user): bool
    {
        return $this->create($user);
    }

    public function sync(User $user, Product $product): bool
    {
        return $this->view($user, $product);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->view($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->view($user, $product);
    }
}
