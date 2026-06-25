<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\User;
use App\Repositories\Contracts\AttributeRepositoryInterface;
use App\Support\PerformanceCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttributeService
{
    public function __construct(private readonly AttributeRepositoryInterface $attributeRepository)
    {
    }

    public function paginateForVendor(User $user): LengthAwarePaginator
    {
        return $this->attributeRepository->paginateForVendor($this->resolveVendorId($user), (int) config('product_variant.pagination.attributes_per_page', 15));
    }

    public function create(User $user, array $data): Attribute
    {
        $vendorId = $this->resolveVendorId($user);

        $attribute = DB::transaction(function () use ($data, $vendorId): Attribute {
            $attribute = $this->attributeRepository->create([
                'vendor_id' => $vendorId,
                'name' => $data['name'],
                'slug' => $data['slug'] ?? Str::slug($data['name']),
                'type' => $data['type'] ?? 'text',
                'is_active' => (bool) ($data['is_active'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);

            $this->syncValues($attribute, $vendorId, $data['values'] ?? []);

            return $attribute->fresh(['values']);
        });

        $this->invalidateVendorAttributeCache($vendorId);

        return $attribute;
    }

    public function update(Attribute $attribute, array $data): Attribute
    {
        $updated = DB::transaction(function () use ($attribute, $data): Attribute {
            $updated = $this->attributeRepository->update($attribute, [
                'name' => $data['name'],
                'slug' => $data['slug'] ?? Str::slug($data['name']),
                'type' => $data['type'] ?? $attribute->type,
                'is_active' => (bool) ($data['is_active'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);

            $this->syncValues($updated, $updated->vendor_id, $data['values'] ?? []);

            return $updated->fresh(['values']);
        });

        $this->invalidateVendorAttributeCache((int) $attribute->vendor_id);

        return $updated;
    }

    public function delete(Attribute $attribute): void
    {
        $this->attributeRepository->delete($attribute);
        $this->invalidateVendorAttributeCache((int) $attribute->vendor_id);
    }

    /**
     * @return Collection<int, Attribute>
     */
    public function getAllForVendor(User $user): Collection
    {
        $vendorId = $this->resolveVendorId($user);
        $namespace = $this->vendorAttributeNamespace($vendorId);
        $ttl = (int) config('performance.cache.ttl.vendor_attributes_seconds', 1800);

        return PerformanceCache::remember($namespace, 'all-with-values', $ttl, function () use ($vendorId) {
            return $this->attributeRepository->findManyWithValuesForVendor(
                $vendorId,
                Attribute::query()->where('vendor_id', $vendorId)->pluck('id')->all()
            );
        });
    }

    /**
     * @param array<int, array<string, mixed>> $values
     */
    private function syncValues(Attribute $attribute, int $vendorId, array $values): void
    {
        $existingById = $attribute->values()->get()->keyBy('id');
        $keptIds = [];

        foreach ($values as $valueData) {
            $id = isset($valueData['id']) ? (int) $valueData['id'] : null;
            $payload = [
                'attribute_id' => $attribute->id,
                'vendor_id' => $vendorId,
                'value' => (string) $valueData['value'],
                'label' => (string) ($valueData['label'] ?? $valueData['value']),
                'code' => $valueData['code'] ?? Str::upper(Str::slug((string) $valueData['value'], '')),
                'is_active' => (bool) ($valueData['is_active'] ?? false),
                'sort_order' => (int) ($valueData['sort_order'] ?? 0),
            ];

            if ($id && $existingById->has($id)) {
                /** @var AttributeValue $attributeValue */
                $attributeValue = $existingById->get($id);
                $attributeValue->fill($payload)->save();
                $keptIds[] = $id;
                continue;
            }

            $newValue = $attribute->values()->create($payload);
            $keptIds[] = $newValue->id;
        }

        if ($keptIds !== []) {
            $attribute->values()->whereNotIn('id', $keptIds)->delete();
            return;
        }

        $attribute->values()->delete();
    }

    private function resolveVendorId(User $user): int
    {
        return (int) ($user->ownedVendor?->id ?? $user->vendor_id);
    }

    private function invalidateVendorAttributeCache(int $vendorId): void
    {
        PerformanceCache::invalidate($this->vendorAttributeNamespace($vendorId));
    }

    private function vendorAttributeNamespace(int $vendorId): string
    {
        $prefix = (string) config('performance.cache.namespaces.vendor_attributes', 'vendor_attributes');

        return $prefix.':'.$vendorId;
    }
}
