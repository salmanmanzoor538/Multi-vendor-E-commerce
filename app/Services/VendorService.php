<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Events\VendorApplied;
use App\Events\VendorApproved;
use App\Events\VendorRejected;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\Contracts\VendorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorService
{
    public function __construct(private readonly VendorRepositoryInterface $vendorRepository)
    {
    }

    public function createApplication(array $data, ?User $user = null): Vendor
    {
        $vendor = DB::transaction(function () use ($data, $user): Vendor {
            $owner = $user ?? User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => UserRole::VENDOR,
            ]);

            $existingVendor = $this->vendorRepository->findByOwnerId($owner->id);

            if ($existingVendor) {
                return $existingVendor;
            }

            if ($owner->role !== UserRole::ADMIN) {
                $owner->forceFill(['role' => UserRole::VENDOR])->save();
            }

            $vendor = $this->vendorRepository->create([
                'user_id' => $owner->id,
                'store_name' => $data['store_name'],
                'store_slug' => $this->resolveStoreSlug($data),
                'status' => VendorStatus::PENDING,
                'business_email' => $data['business_email'] ?? null,
                'business_phone' => $data['business_phone'] ?? null,
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            $owner->forceFill(['vendor_id' => $vendor->id])->save();

            return $vendor;
        });

        event(new VendorApplied($vendor));

        return $vendor;
    }

    public function approve(Vendor $vendor, User $admin): Vendor
    {
        $approvedVendor = DB::transaction(function () use ($vendor, $admin): Vendor {
            $updatedVendor = $this->vendorRepository->update($vendor, [
                'status' => VendorStatus::APPROVED,
                'approved_at' => now(),
                'approved_by' => $admin->id,
                'rejection_reason' => null,
            ]);

            $updatedVendor->owner->forceFill([
                'role' => UserRole::VENDOR,
                'vendor_id' => $updatedVendor->id,
            ])->save();

            return $updatedVendor;
        });

        event(new VendorApproved($approvedVendor));

        return $approvedVendor;
    }

    public function reject(Vendor $vendor, User $admin, ?string $reason = null): Vendor
    {
        $rejectedVendor = DB::transaction(function () use ($vendor, $admin, $reason): Vendor {
            return $this->vendorRepository->update($vendor, [
                'status' => VendorStatus::REJECTED,
                'approved_at' => null,
                'approved_by' => $admin->id,
                'rejection_reason' => $reason,
            ]);
        });

        event(new VendorRejected($rejectedVendor));

        return $rejectedVendor;
    }

    public function updateStore(Vendor $vendor, array $data): Vendor
    {
        return DB::transaction(function () use ($vendor, $data): Vendor {
            return $this->vendorRepository->update($vendor, [
                'store_name' => $data['store_name'],
                'store_slug' => $data['store_slug'] ?? $vendor->store_slug,
                'business_email' => $data['business_email'] ?? null,
                'business_phone' => $data['business_phone'] ?? null,
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        });
    }

    public function getAdminVendors(?string $status = null): LengthAwarePaginator
    {
        $perPage = (int) config('vendor.pagination.admin_per_page', 15);

        return $this->vendorRepository->paginateForAdmin($status, $perPage);
    }

    public function getVendorForOwner(User $owner): ?Vendor
    {
        return $this->vendorRepository->findByOwnerId($owner->id);
    }

    private function resolveStoreSlug(array $data): string
    {
        if (! empty($data['store_slug'])) {
            return $data['store_slug'];
        }

        $baseSlug = Str::slug($data['store_name']);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'store';

        $slug = $baseSlug;
        $iteration = 2;

        while (Vendor::query()->where('store_slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$iteration;
            $iteration++;
        }

        return $slug;
    }
}
