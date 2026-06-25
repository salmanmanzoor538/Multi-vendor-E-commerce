<?php

namespace App\Repositories\Eloquent;

use App\Models\Attribute;
use App\Repositories\Contracts\AttributeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AttributeRepository implements AttributeRepositoryInterface
{
    public function paginateForVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return Attribute::query()
            ->with('values')
            ->where('vendor_id', $vendorId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByVendorAndId(int $vendorId, int $attributeId): ?Attribute
    {
        return Attribute::query()
            ->with('values')
            ->where('vendor_id', $vendorId)
            ->whereKey($attributeId)
            ->first();
    }

    public function create(array $data): Attribute
    {
        return Attribute::query()->create($data);
    }

    public function update(Attribute $attribute, array $data): Attribute
    {
        $attribute->fill($data)->save();

        return $attribute->fresh(['values']);
    }

    public function delete(Attribute $attribute): void
    {
        $attribute->delete();
    }

    public function findManyWithValuesForVendor(int $vendorId, array $attributeIds): Collection
    {
        return Attribute::query()
            ->with(['values' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')])
            ->where('vendor_id', $vendorId)
            ->whereIn('id', $attributeIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
