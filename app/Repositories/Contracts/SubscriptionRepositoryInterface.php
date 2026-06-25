<?php

namespace App\Repositories\Contracts;

use App\Models\Subscription;
use Illuminate\Support\Collection;

interface SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription;

    public function update(Subscription $subscription, array $data): Subscription;

    public function findById(int $id): ?Subscription;

    public function findActiveForVendor(int $vendorId): ?Subscription;

    public function findLatestForVendor(int $vendorId): ?Subscription;

    public function findExpiringBefore(string $timestamp): Collection;

    public function hasActiveForVendor(int $vendorId): bool;
}
