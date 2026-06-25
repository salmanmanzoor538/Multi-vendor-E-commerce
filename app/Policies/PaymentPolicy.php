<?php

namespace App\Policies;

use App\Enums\PaymentProvider;
use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        if ($user->hasRole(UserRole::CUSTOMER)) {
            return (int) $payment->user_id === (int) $user->id;
        }

        if (! $user->hasRole(UserRole::VENDOR) || $user->vendor_id === null) {
            return false;
        }

        $vendorOrderCount = $payment->orders()
            ->where('vendor_id', $user->vendor_id)
            ->count();

        $batchOrderCount = $payment->orders()->count();

        return $batchOrderCount > 0 && $vendorOrderCount === $batchOrderCount;
    }

    public function settleCod(User $user, Payment $payment): bool
    {
        if ($payment->provider !== PaymentProvider::COD) {
            return false;
        }

        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        if (! $user->hasRole(UserRole::VENDOR) || $user->vendor_id === null) {
            return false;
        }

        $batchOrderCount = $payment->orders()->count();
        $vendorOrderCount = $payment->orders()
            ->where('vendor_id', $user->vendor_id)
            ->count();

        return $batchOrderCount > 0 && $vendorOrderCount === $batchOrderCount;
    }
}
