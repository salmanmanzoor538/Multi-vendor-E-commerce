<?php

namespace App\Services;

use App\Models\Plan;
use App\Repositories\Contracts\PlanRepositoryInterface;
use App\Support\PerformanceCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PlanService
{
    public function __construct(private readonly PlanRepositoryInterface $planRepository)
    {
    }

    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator
    {
        return $this->planRepository->paginateForAdmin($perPage);
    }

    public function getActivePlans(): Collection
    {
        $namespace = (string) config('performance.cache.namespaces.plans', 'plans');
        $ttl = (int) config('performance.cache.ttl.plans_seconds', 1800);

        return PerformanceCache::remember($namespace, 'active-ordered', $ttl, fn () => $this->planRepository->getActiveOrdered());
    }

    public function create(array $data): Plan
    {
        $plan = $this->planRepository->create([
            'name' => $data['name'],
            'slug' => $this->resolveSlug($data['name'], $data['slug'] ?? null),
            'description' => $data['description'] ?? null,
            'monthly_price' => $data['monthly_price'],
            'yearly_price' => $data['yearly_price'],
            'limits' => $data['limits'],
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        $this->invalidatePlanCache();

        return $plan;
    }

    public function update(Plan $plan, array $data): Plan
    {
        $updated = $this->planRepository->update($plan, [
            'name' => $data['name'],
            'slug' => $this->resolveSlug($data['name'], $data['slug'] ?? null, $plan->id),
            'description' => $data['description'] ?? null,
            'monthly_price' => $data['monthly_price'],
            'yearly_price' => $data['yearly_price'],
            'limits' => $data['limits'],
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        $this->invalidatePlanCache();

        return $updated;
    }

    public function deactivate(Plan $plan): Plan
    {
        $updated = $this->planRepository->update($plan, [
            'is_active' => false,
        ]);

        $this->invalidatePlanCache();

        return $updated;
    }

    private function resolveSlug(string $name, ?string $slug = null, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($slug ?: $name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'plan';

        $resolvedSlug = $baseSlug;
        $counter = 2;

        while ($this->planRepository->slugExists($resolvedSlug, $ignoreId)) {
            $resolvedSlug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $resolvedSlug;
    }

    private function invalidatePlanCache(): void
    {
        $namespace = (string) config('performance.cache.namespaces.plans', 'plans');

        PerformanceCache::invalidate($namespace);
    }
}
