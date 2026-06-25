<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AttributeValue>
 */
class AttributeValueFactory extends Factory
{
    protected $model = AttributeValue::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $value = fake()->randomElement(['Small', 'Medium', 'Large']);

        return [
            'attribute_id' => Attribute::factory(),
            'vendor_id' => fn (array $attributes) => Attribute::query()->findOrFail($attributes['attribute_id'])->vendor_id,
            'value' => $value,
            'label' => $value,
            'code' => Str::upper(Str::substr(Str::slug($value, ''), 0, 4)),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
