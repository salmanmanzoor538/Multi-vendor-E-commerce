<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->fill($data)->save();

        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        return (bool) $category->delete();
    }

    public function findById(int $id): ?Category
    {
        return Category::query()->find($id);
    }

    public function getTree(): Collection
    {
        return Category::query()
            ->with(['childrenRecursive', 'creator'])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    public function getFlatList(): Collection
    {
        return Category::query()
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Category::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
