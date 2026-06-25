<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Support\PerformanceCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(private readonly CategoryRepositoryInterface $categoryRepository)
    {
    }

    public function getTree(): Collection
    {
        $namespace = (string) config('performance.cache.namespaces.categories', 'categories');
        $ttl = (int) config('performance.cache.ttl.taxonomy_seconds', 1800);

        return PerformanceCache::remember($namespace, 'tree', $ttl, fn () => $this->categoryRepository->getTree());
    }

    public function getFlatTreeOptions(): Collection
    {
        $namespace = (string) config('performance.cache.namespaces.categories', 'categories');
        $ttl = (int) config('performance.cache.ttl.taxonomy_seconds', 1800);

        return PerformanceCache::remember($namespace, 'flat-options', $ttl, fn () => $this->flattenTree($this->getTree()));
    }

    public function create(array $data, User $user): Category
    {
        $category = DB::transaction(function () use ($data, $user): Category {
            $slug = $this->resolveSlug($data['name'], $data['slug'] ?? null);

            return $this->categoryRepository->create([
                'name' => $data['name'],
                'slug' => $slug,
                'parent_id' => $data['parent_id'] ?? null,
                'created_by' => $user->id,
            ]);
        });

        $this->invalidateCategoryCache();

        return $category;
    }

    public function update(Category $category, array $data): Category
    {
        $this->assertValidParent($category, $data['parent_id'] ?? null);

        $updated = DB::transaction(function () use ($category, $data): Category {
            return $this->categoryRepository->update($category, [
                'name' => $data['name'],
                'slug' => $this->resolveSlug($data['name'], $data['slug'] ?? null, $category->id),
                'parent_id' => $data['parent_id'] ?? null,
            ]);
        });

        $this->invalidateCategoryCache();

        return $updated;
    }

    public function delete(Category $category): bool
    {
        $deleted = DB::transaction(function () use ($category): bool {
            Category::query()
                ->where('parent_id', $category->id)
                ->update(['parent_id' => null]);

            return $this->categoryRepository->delete($category);
        });

        $this->invalidateCategoryCache();

        return $deleted;
    }

    public function assertValidParent(Category $category, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ($parentId === $category->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category cannot be its own parent.',
            ]);
        }

        if ($this->isDescendant($category->id, $parentId)) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category cannot be moved under its own child.',
            ]);
        }
    }

    private function flattenTree(Collection $nodes, int $depth = 0): Collection
    {
        $flattened = collect();

        foreach ($nodes as $node) {
            $flattened->push([
                'id' => $node->id,
                'name' => str_repeat('-- ', $depth).$node->name,
            ]);

            if ($node->relationLoaded('childrenRecursive') && $node->childrenRecursive->isNotEmpty()) {
                $flattened = $flattened->merge($this->flattenTree($node->childrenRecursive, $depth + 1));
            }
        }

        return $flattened;
    }

    private function resolveSlug(string $name, ?string $slug = null, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($slug ?: $name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'category';

        $resolvedSlug = $baseSlug;
        $counter = 2;

        while ($this->categoryRepository->slugExists($resolvedSlug, $ignoreId)) {
            $resolvedSlug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $resolvedSlug;
    }

    private function isDescendant(int $categoryId, int $parentId): bool
    {
        $currentParentId = $parentId;

        while ($currentParentId !== null) {
            if ($currentParentId === $categoryId) {
                return true;
            }

            $currentParentId = Category::query()->whereKey($currentParentId)->value('parent_id');
        }

        return false;
    }

    private function invalidateCategoryCache(): void
    {
        $namespace = (string) config('performance.cache.namespaces.categories', 'categories');

        PerformanceCache::invalidate($namespace);
    }
}

