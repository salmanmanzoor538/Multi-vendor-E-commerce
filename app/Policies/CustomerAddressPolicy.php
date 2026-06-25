<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CustomerAddress;
use App\Models\User;

class CustomerAddressPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::CUSTOMER);
    }

    public function view(User $user, CustomerAddress $address): bool
    {
        return $user->hasRole(UserRole::CUSTOMER) && (int) $address->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, CustomerAddress $address): bool
    {
        return $this->view($user, $address);
    }

    public function delete(User $user, CustomerAddress $address): bool
    {
        return $this->view($user, $address);
    }
}
