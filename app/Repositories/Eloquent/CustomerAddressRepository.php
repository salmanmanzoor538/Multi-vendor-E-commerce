<?php

namespace App\Repositories\Eloquent;

use App\Enums\AddressType;
use App\Models\CustomerAddress;
use App\Repositories\Contracts\CustomerAddressRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CustomerAddressRepository implements CustomerAddressRepositoryInterface
{
    public function getForUser(int $userId): Collection
    {
        return CustomerAddress::query()
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->latest()
            ->get();
    }

    public function findForUser(int $userId, int $addressId): ?CustomerAddress
    {
        return CustomerAddress::query()
            ->where('user_id', $userId)
            ->whereKey($addressId)
            ->first();
    }

    public function create(array $data): CustomerAddress
    {
        return CustomerAddress::query()->create($data);
    }

    public function update(CustomerAddress $address, array $data): CustomerAddress
    {
        $address->fill($data)->save();

        return $address->fresh();
    }

    public function delete(CustomerAddress $address): bool
    {
        return (bool) $address->delete();
    }

    public function clearDefaultForType(int $userId, AddressType $type, ?int $exceptId = null): void
    {
        CustomerAddress::query()
            ->where('user_id', $userId)
            ->where('type', $type->value)
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->update(['is_default' => false]);
    }

    public function findDefaultForType(int $userId, AddressType $type): ?CustomerAddress
    {
        return CustomerAddress::query()
            ->where('user_id', $userId)
            ->where('type', $type->value)
            ->where('is_default', true)
            ->first();
    }
}
