<?php

namespace App\Services;

use App\Enums\AddressType;
use App\Models\CustomerAddress;
use App\Models\User;
use App\Repositories\Contracts\CustomerAddressRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AddressService
{
    public function __construct(private readonly CustomerAddressRepositoryInterface $addressRepository)
    {
    }

    public function getForUser(User $user): Collection
    {
        return $this->addressRepository->getForUser($user->id);
    }

    public function create(User $user, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($user, $data): CustomerAddress {
            $type = AddressType::from($data['type']);
            $isDefault = (bool) ($data['is_default'] ?? false);

            if ($isDefault) {
                $this->addressRepository->clearDefaultForType($user->id, $type);
            }

            $address = $this->addressRepository->create([
                ...$data,
                'user_id' => $user->id,
                'country_code' => strtoupper($data['country_code']),
                'is_default' => $isDefault,
            ]);

            if (! $this->addressRepository->findDefaultForType($user->id, $type)) {
                $address = $this->addressRepository->update($address, ['is_default' => true]);
            }

            return $address;
        });
    }

    public function update(User $user, CustomerAddress $address, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($user, $address, $data): CustomerAddress {
            $type = AddressType::from($data['type']);
            $isDefault = (bool) ($data['is_default'] ?? false);

            if ($isDefault) {
                $this->addressRepository->clearDefaultForType($user->id, $type, $address->id);
            }

            $updatedAddress = $this->addressRepository->update($address, [
                ...$data,
                'country_code' => strtoupper($data['country_code']),
                'is_default' => $isDefault,
            ]);

            if (! $this->addressRepository->findDefaultForType($user->id, $type)) {
                $updatedAddress = $this->addressRepository->update($updatedAddress, ['is_default' => true]);
            }

            return $updatedAddress;
        });
    }

    public function delete(User $user, CustomerAddress $address): bool
    {
        return DB::transaction(function () use ($user, $address): bool {
            $type = $address->type;
            $wasDefault = $address->is_default;
            $deleted = $this->addressRepository->delete($address);

            if ($deleted && $wasDefault) {
                $replacement = $this->addressRepository->getForUser($user->id)
                    ->first(fn (CustomerAddress $item) => $item->type === $type);

                if ($replacement) {
                    $this->addressRepository->update($replacement, ['is_default' => true]);
                }
            }

            return $deleted;
        });
    }

    public function resolveForCheckout(User $user, int $addressId, AddressType $type): CustomerAddress
    {
        $address = $this->addressRepository->findForUser($user->id, $addressId);

        abort_unless($address && $address->type === $type, 422, ucfirst($type->value).' address is invalid.');

        return $address;
    }
}
