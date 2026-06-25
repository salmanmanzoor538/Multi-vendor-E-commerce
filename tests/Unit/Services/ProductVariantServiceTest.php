<?php

namespace Tests\Unit\Services;

use App\Enums\ProductStatus;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use App\Repositories\Eloquent\AttributeRepository;
use App\Repositories\Eloquent\ProductVariantRepository;
use App\Services\ProductVariantGenerationService;
use App\Services\ProductVariantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_applies_unique_sku_suffix_when_conflict_exists(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::query()->create([
            'vendor_id' => $vendor->id,
            'name' => 'Test',
            'slug' => 'test',
            'sku' => 'BASE',
            'price' => 100,
            'stock' => 10,
            'status' => ProductStatus::PUBLISHED,
        ]);

        ProductVariant::query()->create([
            'product_id' => $product->id,
            'vendor_id' => $vendor->id,
            'sku' => 'BASE-S',
            'price' => 10,
            'stock_qty' => 1,
            'status' => 'active',
            'combination_key' => '1:1',
            'option_snapshot' => [],
        ]);

        $attribute = Attribute::factory()->create(['vendor_id' => $vendor->id]);
        $value = AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'vendor_id' => $vendor->id, 'code' => 'S']);

        $service = new ProductVariantService(
            new ProductVariantRepository,
            new ProductVariantGenerationService(new AttributeRepository),
        );

        $saved = $service->syncVariants($product, [[
            'combination_key' => '2:2',
            'sku' => 'BASE-S',
            'price' => 20,
            'stock_qty' => 3,
            'status' => 'active',
            'option_snapshot' => [[
                'attribute_id' => $attribute->id,
                'attribute_name' => $attribute->name,
                'attribute_slug' => $attribute->slug,
                'attribute_value_id' => $value->id,
                'attribute_value' => $value->value,
                'attribute_value_label' => $value->label,
                'attribute_value_code' => $value->code,
            ]],
            'values' => [[
                'attribute_id' => $attribute->id,
                'attribute_value_id' => $value->id,
            ]],
        ]]);

        $this->assertSame('BASE-S-1', $saved[0]->sku);
    }
}
