<?php

namespace Tests\Feature\Vendor;

use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_endpoint_returns_cartesian_variants(): void
    {
        [$user, $vendor] = $this->createApprovedVendorUser();

        $size = Attribute::query()->create([
            'vendor_id' => $vendor->id,
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'text',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $color = Attribute::query()->create([
            'vendor_id' => $vendor->id,
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'text',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $small = AttributeValue::query()->create(['attribute_id' => $size->id, 'vendor_id' => $vendor->id, 'value' => 'small', 'label' => 'Small', 'code' => 'S', 'is_active' => true, 'sort_order' => 1]);
        $large = AttributeValue::query()->create(['attribute_id' => $size->id, 'vendor_id' => $vendor->id, 'value' => 'large', 'label' => 'Large', 'code' => 'L', 'is_active' => true, 'sort_order' => 2]);
        $red = AttributeValue::query()->create(['attribute_id' => $color->id, 'vendor_id' => $vendor->id, 'value' => 'red', 'label' => 'Red', 'code' => 'RED', 'is_active' => true, 'sort_order' => 1]);
        $blue = AttributeValue::query()->create(['attribute_id' => $color->id, 'vendor_id' => $vendor->id, 'value' => 'blue', 'label' => 'Blue', 'code' => 'BLU', 'is_active' => true, 'sort_order' => 2]);

        $response = $this->actingAs($user)->postJson(route('vendor.products.variants.generate'), [
            'base_sku' => 'TSHIRT',
            'attributes' => [
                ['attribute_id' => $size->id, 'value_ids' => [$small->id, $large->id]],
                ['attribute_id' => $color->id, 'value_ids' => [$red->id, $blue->id]],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonCount(4, 'variants');
        $response->assertJsonPath('variants.0.generated_sku', 'TSHIRT-S-RED');
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
