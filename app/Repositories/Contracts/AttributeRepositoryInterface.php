<?php

namespace App\Repositories\Contracts;

use App\Models\Attribute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AttributeRepositoryInterface
{
    public function paginateForVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator;

    public function findByVendorAndId(int $vendorId, int $attributeId): ?Attribute;

    public function create(array $data): Attribute;

    public function update(Attribute $attribute, array $data): Attribute;

    public function delete(Attribute $attribute): void;

    /**
     * @return Collection<int, Attribute>
     */
    public function findManyWithValuesForVendor(int $vendorId, array $attributeIds): Collection;
}
