<?php

namespace Database\Factories;

use App\Enums\VendorStatus;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $storeName = fake()->company();

        return [
            'user_id' => User::factory(),
            'store_name' => $storeName,
            'store_slug' => Str::slug($storeName).'-'.fake()->unique()->numberBetween(100, 999),
            'status' => VendorStatus::APPROVED,
        ];
    }
}
