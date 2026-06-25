<?php

namespace Tests\Feature\Vendor;

use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Models\Attribute;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_create_attribute_with_values(): void
    {
        [$user, $vendor] = $this->createApprovedVendorUser();

        $response = $this->actingAs($user)->post(route('vendor.attributes.store'), [
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'text',
            'is_active' => true,
            'values' => [
                ['value' => 'Small', 'label' => 'Small', 'code' => 'S'],
                ['value' => 'Medium', 'label' => 'Medium', 'code' => 'M'],
            ],
        ]);

        $response->assertRedirect(route('vendor.attributes.index'));
        $this->assertDatabaseHas('attributes', [
            'vendor_id' => $vendor->id,
            'slug' => 'size',
        ]);
        $this->assertDatabaseHas('attribute_values', [
            'vendor_id' => $vendor->id,
            'value' => 'Small',
        ]);
    }

    public function test_vendor_cannot_update_another_vendor_attribute(): void
    {
        [$user] = $this->createApprovedVendorUser();
        [$otherUser, $otherVendor] = $this->createApprovedVendorUser();

        $attribute = Attribute::query()->create([
            'vendor_id' => $otherVendor->id,
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'text',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($user)->patch(route('vendor.attributes.update', $attribute), [
            'name' => 'Color Updated',
            'slug' => 'color-updated',
            'type' => 'text',
            'is_active' => true,
            'values' => [
                ['value' => 'Red', 'label' => 'Red', 'code' => 'RED'],
            ],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'name' => 'Color',
        ]);

        unset($otherUser);
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
