<?php

namespace App\Repositories\Contracts;

use App\Enums\AddressType;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Collection;

interface CustomerAddressRepositoryInterface
{
    public function getForUser(int $userId): Collection;

    public function findForUser(int $userId, int $addressId): ?CustomerAddress;

    public function create(array $data): CustomerAddress;

    public function update(CustomerAddress $address, array $data): CustomerAddress;

    public function delete(CustomerAddress $address): bool;

    public function clearDefaultForType(int $userId, AddressType $type, ?int $exceptId = null): void;

    public function findDefaultForType(int $userId, AddressType $type): ?CustomerAddress;
}
