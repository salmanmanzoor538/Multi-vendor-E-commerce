<?php

namespace Database\Factories;

use App\Enums\ProductVariantStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'vendor_id' => fn (array $attributes) => Product::query()->findOrFail($attributes['product_id'])->vendor_id,
            'sku' => strtoupper(fake()->bothify('VR-####')),
            'price' => fake()->randomFloat(2, 10, 300),
            'stock_qty' => fake()->numberBetween(0, 100),
            'status' => ProductVariantStatus::ACTIVE,
            'combination_key' => fake()->unique()->numerify('1:##|2:##'),
            'option_snapshot' => [],
        ];
    }
}
