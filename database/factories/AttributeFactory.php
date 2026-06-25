<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Attribute>
 */
class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Size', 'Color', 'Material']);

        return [
            'vendor_id' => Vendor::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999),
            'type' => 'text',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
