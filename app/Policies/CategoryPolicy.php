<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::ADMIN, UserRole::VENDOR]);
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasAnyRole([UserRole::ADMIN, UserRole::VENDOR]);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([UserRole::ADMIN, UserRole::VENDOR]);
    }

    public function update(User $user, Category $category): bool
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        return $user->hasRole(UserRole::VENDOR) && $category->created_by === $user->id;
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->update($user, $category);
    }
}
