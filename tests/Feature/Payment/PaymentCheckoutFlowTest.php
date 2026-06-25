<?php

namespace Tests\Feature\Payment;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Models\CartItem;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_place_checkout_with_stripe(): void
    {
        config([
            'services.stripe.secret_key' => 'sk_test_123',
        ]);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.test/session/cs_test_123',
                'payment_intent' => 'pi_test_123',
            ], 200),
        ]);

        [$customer] = $this->createCustomerCheckoutContext();

        $response = $this->actingAs($customer)->post(route('customer.checkout.place'), [
            'shipping_address_id' => $customer->customerAddresses()->where('type', 'shipping')->value('id'),
            'billing_address_id' => $customer->customerAddresses()->where('type', 'billing')->value('id'),
            'provider' => 'stripe',
        ]);

        $response->assertRedirect('https://checkout.stripe.test/session/cs_test_123');
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', [
            'payment_provider' => 'stripe',
            'payment_status' => PaymentStatus::PENDING->value,
            'status' => OrderStatus::PENDING->value,
        ]);
        $this->assertDatabaseHas('payments', [
            'provider' => 'stripe',
            'status' => PaymentStatus::PENDING->value,
            'stripe_session_id' => 'cs_test_123',
            'stripe_payment_intent_id' => 'pi_test_123',
        ]);
    }

    public function test_customer_can_place_checkout_with_cod(): void
    {
        [$customer] = $this->createCustomerCheckoutContext();

        $response = $this->actingAs($customer)->post(route('customer.checkout.place'), [
            'shipping_address_id' => $customer->customerAddresses()->where('type', 'shipping')->value('id'),
            'billing_address_id' => $customer->customerAddresses()->where('type', 'billing')->value('id'),
            'provider' => 'cod',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString(route('customer.checkout.success', [], false), (string) $response->headers->get('Location'));
        $this->assertDatabaseHas('payments', [
            'provider' => 'cod',
            'status' => PaymentStatus::PENDING->value,
        ]);
        $this->assertDatabaseHas('orders', [
            'payment_provider' => 'cod',
            'payment_status' => PaymentStatus::PENDING->value,
            'status' => OrderStatus::PENDING->value,
        ]);
    }

    public function test_stripe_webhook_marks_payment_and_orders_paid(): void
    {
        config([
            'services.stripe.webhook_secret' => 'whsec_test',
            'checkout.stripe.webhook_tolerance_seconds' => 300,
        ]);

        [$customer, $vendor] = $this->createCustomerAndVendor();
        $batchId = (string) fake()->uuid();

        Order::query()->create([
            'checkout_batch_id' => $batchId,
            'order_number' => 'ORD-'.strtoupper(fake()->bothify('########')),
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'payment_provider' => 'stripe',
            'payment_reference' => 'cs_test_webhook',
            'payment_status' => PaymentStatus::PENDING->value,
            'status' => OrderStatus::PENDING->value,
            'currency' => 'USD',
            'subtotal' => 10,
            'shipping_amount' => 2,
            'grand_total' => 12,
            'shipping_address_snapshot' => ['line_1' => 'A'],
            'billing_address_snapshot' => ['line_1' => 'B'],
        ]);

        Payment::query()->create([
            'checkout_batch_id' => $batchId,
            'user_id' => $customer->id,
            'provider' => 'stripe',
            'status' => PaymentStatus::PENDING->value,
            'currency' => 'USD',
            'amount' => 12,
            'gateway_reference' => 'cs_test_webhook',
            'stripe_session_id' => 'cs_test_webhook',
        ]);

        $payload = json_encode([
            'id' => 'evt_test_paid',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_webhook',
                    'payment_intent' => 'pi_test_webhook',
                    'metadata' => [
                        'checkout_batch_id' => $batchId,
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test');

        $response = $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'HTTP_Stripe-Signature' => "t={$timestamp},v1={$signature}",
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $response->assertOk();
        $this->assertDatabaseHas('payments', [
            'checkout_batch_id' => $batchId,
            'status' => PaymentStatus::PAID->value,
            'stripe_payment_intent_id' => 'pi_test_webhook',
        ]);
        $this->assertDatabaseHas('orders', [
            'checkout_batch_id' => $batchId,
            'payment_status' => PaymentStatus::PAID->value,
            'status' => OrderStatus::CONFIRMED->value,
        ]);
    }

    public function test_stripe_failed_webhook_marks_payment_and_orders_failed(): void
    {
        config([
            'services.stripe.webhook_secret' => 'whsec_test',
            'checkout.stripe.webhook_tolerance_seconds' => 300,
        ]);

        [$customer, $vendor] = $this->createCustomerAndVendor();
        $batchId = (string) fake()->uuid();

        Order::query()->create([
            'checkout_batch_id' => $batchId,
            'order_number' => 'ORD-'.strtoupper(fake()->bothify('########')),
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'payment_provider' => 'stripe',
            'payment_reference' => 'pi_test_failed',
            'payment_status' => PaymentStatus::PENDING->value,
            'status' => OrderStatus::PENDING->value,
            'currency' => 'USD',
            'subtotal' => 10,
            'shipping_amount' => 2,
            'grand_total' => 12,
            'shipping_address_snapshot' => ['line_1' => 'A'],
            'billing_address_snapshot' => ['line_1' => 'B'],
        ]);

        Payment::query()->create([
            'checkout_batch_id' => $batchId,
            'user_id' => $customer->id,
            'provider' => 'stripe',
            'status' => PaymentStatus::PENDING->value,
            'currency' => 'USD',
            'amount' => 12,
            'gateway_reference' => 'pi_test_failed',
            'stripe_payment_intent_id' => 'pi_test_failed',
        ]);

        $payload = json_encode([
            'id' => 'evt_test_failed',
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_test_failed',
                    'metadata' => [
                        'checkout_batch_id' => $batchId,
                    ],
                    'last_payment_error' => [
                        'message' => 'Card declined',
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test');

        $response = $this->call(
            'POST',
            route('webhooks.stripe'),
            [],
            [],
            [],
            [
                'HTTP_Stripe-Signature' => "t={$timestamp},v1={$signature}",
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $response->assertOk();
        $this->assertDatabaseHas('payments', [
            'checkout_batch_id' => $batchId,
            'status' => PaymentStatus::FAILED->value,
            'failure_reason' => 'Card declined',
        ]);
        $this->assertDatabaseHas('orders', [
            'checkout_batch_id' => $batchId,
            'payment_status' => PaymentStatus::FAILED->value,
            'status' => OrderStatus::CANCELLED->value,
        ]);
    }

    public function test_vendor_cannot_settle_cod_for_mixed_vendor_batch_and_admin_can(): void
    {
        [$customer, $vendorA] = $this->createCustomerAndVendor();
        [, $vendorB] = $this->createCustomerAndVendor();

        $vendorUser = User::factory()->create([
            'role' => UserRole::VENDOR->value,
            'vendor_id' => $vendorA->id,
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN->value,
        ]);

        $batchId = (string) fake()->uuid();

        foreach ([$vendorA, $vendorB] as $vendor) {
            Order::query()->create([
                'checkout_batch_id' => $batchId,
                'order_number' => 'ORD-'.strtoupper(fake()->bothify('########')),
                'user_id' => $customer->id,
                'vendor_id' => $vendor->id,
                'payment_provider' => 'cod',
                'payment_reference' => 'COD-'.$batchId,
                'payment_status' => PaymentStatus::PENDING->value,
                'status' => OrderStatus::PENDING->value,
                'currency' => 'USD',
                'subtotal' => 10,
                'shipping_amount' => 2,
                'grand_total' => 12,
                'shipping_address_snapshot' => ['line_1' => 'A'],
                'billing_address_snapshot' => ['line_1' => 'B'],
            ]);
        }

        Payment::query()->create([
            'checkout_batch_id' => $batchId,
            'user_id' => $customer->id,
            'provider' => 'cod',
            'status' => PaymentStatus::PENDING->value,
            'currency' => 'USD',
            'amount' => 24,
            'gateway_reference' => 'COD-'.$batchId,
        ]);

        $this->actingAs($vendorUser)
            ->post(route('payments.cod.settle', ['checkoutBatchId' => $batchId]))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('payments.cod.settle', ['checkoutBatchId' => $batchId]))
            ->assertOk();

        $this->assertDatabaseHas('payments', [
            'checkout_batch_id' => $batchId,
            'status' => PaymentStatus::PAID->value,
        ]);
    }

    public function test_customer_cannot_settle_cod_payment(): void
    {
        [$customer, $vendor] = $this->createCustomerAndVendor();
        $otherCustomer = User::factory()->create([
            'role' => UserRole::CUSTOMER->value,
        ]);

        $batchId = (string) fake()->uuid();

        Order::query()->create([
            'checkout_batch_id' => $batchId,
            'order_number' => 'ORD-'.strtoupper(fake()->bothify('########')),
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'payment_provider' => 'cod',
            'payment_reference' => 'COD-'.$batchId,
            'payment_status' => PaymentStatus::PENDING->value,
            'status' => OrderStatus::PENDING->value,
            'currency' => 'USD',
            'subtotal' => 10,
            'shipping_amount' => 2,
            'grand_total' => 12,
            'shipping_address_snapshot' => ['line_1' => 'A'],
            'billing_address_snapshot' => ['line_1' => 'B'],
        ]);

        Payment::query()->create([
            'checkout_batch_id' => $batchId,
            'user_id' => $customer->id,
            'provider' => 'cod',
            'status' => PaymentStatus::PENDING->value,
            'currency' => 'USD',
            'amount' => 12,
            'gateway_reference' => 'COD-'.$batchId,
        ]);

        $this->actingAs($otherCustomer)
            ->post(route('payments.cod.settle', ['checkoutBatchId' => $batchId]))
            ->assertForbidden();
    }

    /**
     * @return array{0:User,1:Vendor}
     */
    private function createCustomerAndVendor(): array
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER->value,
        ]);

        $vendorOwner = User::factory()->create([
            'role' => UserRole::VENDOR->value,
        ]);

        $vendor = Vendor::factory()->create([
            'user_id' => $vendorOwner->id,
            'status' => VendorStatus::APPROVED->value,
        ]);

        $vendorOwner->forceFill(['vendor_id' => $vendor->id])->save();

        return [$customer, $vendor];
    }

    /**
     * @return array{0:User,1:Vendor,2:Product}
     */
    private function createCustomerCheckoutContext(): array
    {
        [$customer, $vendor] = $this->createCustomerAndVendor();

        $product = Product::query()->create([
            'vendor_id' => $vendor->id,
            'name' => 'Test Product',
            'slug' => 'test-product-'.fake()->unique()->numberBetween(100, 999),
            'sku' => 'TEST-'.fake()->unique()->numberBetween(100, 999),
            'description' => 'Test',
            'price' => 10.00,
            'stock' => 10,
            'status' => ProductStatus::PUBLISHED->value,
        ]);

        CartItem::query()->create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'item_key' => 'item-'.$product->id,
            'quantity' => 1,
            'unit_price' => 10.00,
            'line_total' => 10.00,
            'currency' => 'USD',
            'name_snapshot' => $product->name,
            'sku_snapshot' => $product->sku,
        ]);

        CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'type' => 'shipping',
            'full_name' => 'John Doe',
            'phone' => '123456',
            'line_1' => 'Street 1',
            'city' => 'City',
            'state' => 'State',
            'postal_code' => '12345',
            'country_code' => 'US',
            'is_default' => true,
        ]);

        CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'type' => 'billing',
            'full_name' => 'John Doe',
            'phone' => '123456',
            'line_1' => 'Street 1',
            'city' => 'City',
            'state' => 'State',
            'postal_code' => '12345',
            'country_code' => 'US',
            'is_default' => true,
        ]);

        return [$customer, $vendor, $product];
    }
}

