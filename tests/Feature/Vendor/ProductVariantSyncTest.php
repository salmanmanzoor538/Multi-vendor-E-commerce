<?php

namespace Tests\Feature\Vendor;

use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_creates_and_deactivates_variants(): void
    {
        [$user, $vendor] = $this->createApprovedVendorUser();
        $product = Product::query()->create([
            'vendor_id' => $vendor->id,
            'name' => 'T-Shirt',
            'slug' => 't-shirt',
            'sku' => 'TSHIRT',
            'price' => 100,
            'stock' => 5,
            'status' => ProductStatus::PUBLISHED,
        ]);

        $attribute = Attribute::query()->create([
            'vendor_id' => $vendor->id,
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'text',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $small = AttributeValue::query()->create(['attribute_id' => $attribute->id, 'vendor_id' => $vendor->id, 'value' => 'small', 'label' => 'Small', 'code' => 'S', 'is_active' => true, 'sort_order' => 1]);
        $medium = AttributeValue::query()->create(['attribute_id' => $attribute->id, 'vendor_id' => $vendor->id, 'value' => 'medium', 'label' => 'Medium', 'code' => 'M', 'is_active' => true, 'sort_order' => 2]);

        $payload = [
            'variants' => [
                [
                    'combination_key' => $attribute->id.':'.$small->id,
                    'sku' => 'TSHIRT-S',
                    'price' => 120,
                    'stock_qty' => 10,
                    'status' => 'active',
                    'option_snapshot' => [[
                        'attribute_id' => $attribute->id,
                        'attribute_name' => 'Size',
                        'attribute_slug' => 'size',
                        'attribute_value_id' => $small->id,
                        'attribute_value' => 'small',
                        'attribute_value_label' => 'Small',
                        'attribute_value_code' => 'S',
                    ]],
                    'values' => [[
                        'attribute_id' => $attribute->id,
                        'attribute_value_id' => $small->id,
                    ]],
                ],
                [
                    'combination_key' => $attribute->id.':'.$medium->id,
                    'sku' => 'TSHIRT-M',
                    'price' => 125,
                    'stock_qty' => 8,
                    'status' => 'active',
                    'option_snapshot' => [[
                        'attribute_id' => $attribute->id,
                        'attribute_name' => 'Size',
                        'attribute_slug' => 'size',
                        'attribute_value_id' => $medium->id,
                        'attribute_value' => 'medium',
                        'attribute_value_label' => 'Medium',
                        'attribute_value_code' => 'M',
                    ]],
                    'values' => [[
                        'attribute_id' => $attribute->id,
                        'attribute_value_id' => $medium->id,
                    ]],
                ],
            ],
        ];

        $this->actingAs($user)->post(route('vendor.products.variants.sync', $product), $payload)->assertRedirect();

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'TSHIRT-S',
            'status' => 'active',
        ]);

        $this->actingAs($user)->post(route('vendor.products.variants.sync', $product), [
            'variants' => [$payload['variants'][0]],
        ])->assertRedirect();

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'TSHIRT-M',
            'status' => 'inactive',
        ]);
    }

    /**
     * @return array{0:User, 1:Vendor}
     */
    private function createApprovedVendorUser(): array
    {
        $user = User::factory()->create([
            'role' => UserRole::VENDOR,
        ]);

        $vendor = Vendor::factory()->create([
            'user_id' => $user->id,
            'status' => VendorStatus::APPROVED,
        ]);

        $user->forceFill(['vendor_id' => $vendor->id])->save();

        return [$user, $vendor];
    }
}
