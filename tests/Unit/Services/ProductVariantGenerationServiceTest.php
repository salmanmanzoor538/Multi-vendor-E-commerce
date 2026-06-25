<?php

namespace Tests\Unit\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Repositories\Eloquent\AttributeRepository;
use App\Services\ProductVariantGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantGenerationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_sku_uses_base_and_codes(): void
    {
        $service = new ProductVariantGenerationService(new AttributeRepository);
        $sku = $service->buildSku('TSHIRT', ['s', 'red']);

        $this->assertSame('TSHIRT-S-RED', $sku);
    }

    public function test_combination_key_is_stable_even_if_attribute_order_changes(): void
    {
        $vendor = \App\Models\Vendor::factory()->create();
        $size = Attribute::factory()->create(['vendor_id' => $vendor->id, 'sort_order' => 2]);
        $color = Attribute::factory()->create(['vendor_id' => $vendor->id, 'sort_order' => 1]);

        $sizeValue = AttributeValue::factory()->create(['attribute_id' => $size->id, 'vendor_id' => $vendor->id]);
        $colorValue = AttributeValue::factory()->create(['attribute_id' => $color->id, 'vendor_id' => $vendor->id]);

        $service = new ProductVariantGenerationService(new AttributeRepository);

        $one = $service->generateForVendor($vendor->id, [
            ['attribute_id' => $size->id, 'value_ids' => [$sizeValue->id]],
            ['attribute_id' => $color->id, 'value_ids' => [$colorValue->id]],
        ]);

        $two = $service->generateForVendor($vendor->id, [
            ['attribute_id' => $color->id, 'value_ids' => [$colorValue->id]],
            ['attribute_id' => $size->id, 'value_ids' => [$sizeValue->id]],
        ]);

        $this->assertSame($one[0]['combination_key'], $two[0]['combination_key']);
    }
}
