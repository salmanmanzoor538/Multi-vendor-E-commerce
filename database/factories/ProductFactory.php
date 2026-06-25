<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'vendor_id' => Vendor::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'sku' => strtoupper(fake()->bothify('PRD-####')),
            'price' => fake()->randomFloat(2, 10, 300),
            'stock' => fake()->numberBetween(1, 100),
            'status' => ProductStatus::PUBLISHED,
        ];
    }
}
