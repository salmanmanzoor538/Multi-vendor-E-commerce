<?php

namespace App\Services;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Events\SubscriptionActivated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionExpired;
use App\Events\SubscriptionRenewed;
use App\Models\Subscription;
use App\Repositories\Contracts\PlanRepositoryInterface;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use App\Support\PerformanceCache;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly PlanRepositoryInterface $planRepository,
    ) {
    }

    public function getActiveForVendor(int $vendorId): ?Subscription
    {
        $namespace = $this->vendorSubscriptionNamespace($vendorId);
        $ttl = (int) config('performance.cache.ttl.vendor_subscription_seconds', 300);

        $subscriptionId = PerformanceCache::remember($namespace, 'active-subscription-id', $ttl, function () use ($vendorId): ?int {
            return $this->subscriptionRepository->findActiveForVendor($vendorId)?->id;
        });

        if (! is_int($subscriptionId)) {
            return null;
        }

        return $this->subscriptionRepository->findById($subscriptionId);
    }

    public function subscribe(int $vendorId, int $planId, BillingCycle $billingCycle): Subscription
    {
        $subscription = DB::transaction(function () use ($vendorId, $planId, $billingCycle): Subscription {
            if ($this->subscriptionRepository->hasActiveForVendor($vendorId)) {
                throw ValidationException::withMessages([
                    'subscription' => 'You already have an active subscription. Please renew or cancel it first.',
                ]);
            }

            $plan = $this->planRepository->findActiveById($planId);
            if (! $plan) {
                throw ValidationException::withMessages([
                    'plan_id' => 'Selected plan is not available.',
                ]);
            }

            $startsAt = CarbonImmutable::now();
            $endsAt = $this->resolveEndsAt($startsAt, $billingCycle);

            return $this->subscriptionRepository->create([
                'vendor_id' => $vendorId,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle->value,
                'status' => SubscriptionStatus::ACTIVE->value,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'cancelled_at' => null,
            ]);
        });

        $subscription->load('plan');
        $this->invalidateVendorSubscriptionCache($vendorId);
        event(new SubscriptionActivated($subscription));

        return $subscription;
    }

    public function renew(int $vendorId, BillingCycle $billingCycle): Subscription
    {
        $renewedSubscription = DB::transaction(function () use ($vendorId, $billingCycle): Subscription {
            $active = $this->subscriptionRepository->findActiveForVendor($vendorId);
            if (! $active) {
                throw ValidationException::withMessages([
                    'subscription' => 'No active subscription found to renew.',
                ]);
            }

            $baseTime = CarbonImmutable::parse($active->ends_at)->greaterThan(now())
                ? CarbonImmutable::parse($active->ends_at)
                : CarbonImmutable::now();

            $endsAt = $this->resolveEndsAt($baseTime, $billingCycle);

            return $this->subscriptionRepository->update($active, [
                'billing_cycle' => $billingCycle->value,
                'ends_at' => $endsAt,
            ]);
        });

        $renewedSubscription->load('plan');
        $this->invalidateVendorSubscriptionCache($vendorId);
        event(new SubscriptionRenewed($renewedSubscription));

        return $renewedSubscription;
    }

    public function cancel(int $vendorId): Subscription
    {
        $subscription = DB::transaction(function () use ($vendorId): Subscription {
            $active = $this->subscriptionRepository->findActiveForVendor($vendorId);

            if (! $active) {
                throw ValidationException::withMessages([
                    'subscription' => 'No active subscription found to cancel.',
                ]);
            }

            return $this->subscriptionRepository->update($active, [
                'status' => SubscriptionStatus::CANCELLED->value,
                'cancelled_at' => now(),
                'ends_at' => now(),
            ]);
        });

        $subscription->load('plan');
        $this->invalidateVendorSubscriptionCache($vendorId);
        event(new SubscriptionCancelled($subscription));

        return $subscription;
    }

    public function expireDueSubscriptions(): Collection
    {
        $expired = collect();

        DB::transaction(function () use (&$expired): void {
            $due = $this->subscriptionRepository->findExpiringBefore(now()->toDateTimeString());

            foreach ($due as $subscription) {
                $expiredSubscription = $this->subscriptionRepository->update($subscription, [
                    'status' => SubscriptionStatus::EXPIRED->value,
                ]);

                $expired->push($expiredSubscription);
            }
        });

        foreach ($expired as $subscription) {
            $subscription->load('plan');
            $this->invalidateVendorSubscriptionCache((int) $subscription->vendor_id);
            event(new SubscriptionExpired($subscription));
        }

        return $expired;
    }

    private function resolveEndsAt(CarbonImmutable $startsAt, BillingCycle $billingCycle): CarbonImmutable
    {
        $monthsByCycle = config('subscription.billing_cycle_months', []);
        $months = max(1, (int) ($monthsByCycle[$billingCycle->value] ?? 1));

        return $startsAt->addMonthsNoOverflow($months);
    }

    private function invalidateVendorSubscriptionCache(int $vendorId): void
    {
        PerformanceCache::invalidate($this->vendorSubscriptionNamespace($vendorId));
    }

    private function vendorSubscriptionNamespace(int $vendorId): string
    {
        $prefix = (string) config('performance.cache.namespaces.vendor_subscription', 'vendor_subscription');

        return $prefix.':'.$vendorId;
    }
}
