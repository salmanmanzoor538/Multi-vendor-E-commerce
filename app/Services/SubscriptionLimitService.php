<?php

namespace App\Services;

use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use App\Support\PerformanceCache;
use Illuminate\Validation\ValidationException;

class SubscriptionLimitService
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly VendorUsageService $vendorUsageService,
    ) {
    }

    public function hasActiveSubscription(int $vendorId): bool
    {
        return $this->activeSubscriptionPayload($vendorId) !== null;
    }

    public function assertCanCreateProduct(int $vendorId): void
    {
        $limit = $this->getLimit($vendorId, config('subscription.limit_keys.product_count', 'product_count'));

        if ($limit === null) {
            return;
        }

        $currentCount = $this->vendorUsageService->getProductCount($vendorId);

        if ($currentCount >= $limit) {
            throw ValidationException::withMessages([
                'subscription' => config('subscription.messages.product_limit_reached'),
            ]);
        }
    }

    public function assertCanUploadBytes(int $vendorId, int $incomingBytes): void
    {
        if ($incomingBytes <= 0) {
            return;
        }

        $limitMb = $this->getLimit($vendorId, config('subscription.limit_keys.storage_mb', 'storage_mb'));

        if ($limitMb === null) {
            return;
        }

        $limitBytes = $limitMb * 1024 * 1024;
        $currentBytes = $this->vendorUsageService->getStorageBytes($vendorId);

        if (($currentBytes + $incomingBytes) > $limitBytes) {
            throw ValidationException::withMessages([
                'subscription' => config('subscription.messages.storage_limit_reached'),
            ]);
        }
    }

    public function getLimit(int $vendorId, string $key): ?int
    {
        $payload = $this->activeSubscriptionPayload($vendorId);
        if ($payload === null) {
            return null;
        }

        $limits = $payload['limits'] ?? [];

        if (! array_key_exists($key, $limits) || $limits[$key] === null) {
            return null;
        }

        return (int) $limits[$key];
    }

    /**
     * @return array{limits: array<string, mixed>}|null
     */
    private function activeSubscriptionPayload(int $vendorId): ?array
    {
        $namespace = $this->vendorSubscriptionNamespace($vendorId);
        $ttl = (int) config('performance.cache.ttl.vendor_subscription_seconds', 300);

        return PerformanceCache::remember($namespace, 'active-payload', $ttl, function () use ($vendorId): ?array {
            $subscription = $this->subscriptionRepository->findActiveForVendor($vendorId);

            if (! $subscription || ! $subscription->plan) {
                return null;
            }

            return [
                'limits' => is_array($subscription->plan->limits) ? $subscription->plan->limits : [],
            ];
        });
    }

    private function vendorSubscriptionNamespace(int $vendorId): string
    {
        $prefix = (string) config('performance.cache.namespaces.vendor_subscription', 'vendor_subscription');

        return $prefix.':'.$vendorId;
    }
}
