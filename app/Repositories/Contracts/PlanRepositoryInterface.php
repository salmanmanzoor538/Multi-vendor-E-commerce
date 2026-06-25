<?php

namespace App\Repositories\Contracts;

use App\Models\Plan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PlanRepositoryInterface
{
    public function create(array $data): Plan;

    public function update(Plan $plan, array $data): Plan;

    public function delete(Plan $plan): bool;

    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator;

    public function getActiveOrdered(): Collection;

    public function findById(int $id): ?Plan;

    public function findActiveById(int $id): ?Plan;

    public function slugExists(string $slug, ?int $ignoreId = null): bool;
}
