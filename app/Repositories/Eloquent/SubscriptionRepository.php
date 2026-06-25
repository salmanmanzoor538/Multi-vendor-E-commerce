<?php

namespace App\Repositories\Eloquent;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use Illuminate\Support\Collection;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription
    {
        return Subscription::query()->create($data);
    }

    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->fill($data)->save();

        return $subscription->fresh();
    }

    public function findById(int $id): ?Subscription
    {
        return Subscription::query()->with('plan')->find($id);
    }

    public function findActiveForVendor(int $vendorId): ?Subscription
    {
        return Subscription::query()
            ->with('plan')
            ->where('vendor_id', $vendorId)
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->where('ends_at', '>', now())
            ->orderByDesc('ends_at')
            ->first();
    }

    public function findLatestForVendor(int $vendorId): ?Subscription
    {
        return Subscription::query()
            ->with('plan')
            ->where('vendor_id', $vendorId)
            ->latest('ends_at')
            ->first();
    }

    public function findExpiringBefore(string $timestamp): Collection
    {
        return Subscription::query()
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->where('ends_at', '<', $timestamp)
            ->get();
    }

    public function hasActiveForVendor(int $vendorId): bool
    {
        return Subscription::query()
            ->where('vendor_id', $vendorId)
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->where('ends_at', '>', now())
            ->exists();
    }
}
