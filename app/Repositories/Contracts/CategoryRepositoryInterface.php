<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function create(array $data): Category;

    public function update(Category $category, array $data): Category;

    public function delete(Category $category): bool;

    public function findById(int $id): ?Category;

    public function getTree(): Collection;

    public function getFlatList(): Collection;

    public function slugExists(string $slug, ?int $ignoreId = null): bool;
}
