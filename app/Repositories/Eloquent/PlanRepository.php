<?php

namespace App\Repositories\Eloquent;

use App\Models\Plan;
use App\Repositories\Contracts\PlanRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PlanRepository implements PlanRepositoryInterface
{
    public function create(array $data): Plan
    {
        return Plan::query()->create($data);
    }

    public function update(Plan $plan, array $data): Plan
    {
        $plan->fill($data)->save();

        return $plan->fresh();
    }

    public function delete(Plan $plan): bool
    {
        return (bool) $plan->delete();
    }

    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator
    {
        return Plan::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getActiveOrdered(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?Plan
    {
        return Plan::query()->find($id);
    }

    public function findActiveById(int $id): ?Plan
    {
        return Plan::query()->where('is_active', true)->find($id);
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Plan::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
