<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        /** @var array<string, array{name: string, email: string, password: string}> $seedUsers */
        $seedUsers = config('multivendor.seed_users', []);

        foreach ($seedUsers as $role => $seedUser) {
            $user = User::query()->updateOrCreate(
                ['email' => $seedUser['email']],
                [
                    'name' => $seedUser['name'],
                    'password' => Hash::make($seedUser['password']),
                    'email_verified_at' => now(),
                    'role' => UserRole::from($role),
                ],
            );

            if ($role === UserRole::VENDOR->value) {
                $vendor = Vendor::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'store_name' => $seedUser['name'].' Store',
                        'store_slug' => Str::slug($seedUser['name'].' Store'),
                        'status' => VendorStatus::APPROVED,
                        'approved_at' => now(),
                    ],
                );

                $user->forceFill(['vendor_id' => $vendor->id])->save();
            }
        }
    }
}
