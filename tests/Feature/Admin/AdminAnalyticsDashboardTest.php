<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard_and_analytics_endpoint(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($admin)
            ->getJson(route('admin.analytics.index'))
            ->assertOk()
            ->assertJsonStructure([
                'kpis' => ['mrr', 'total_sales', 'orders_count'],
                'trends' => [
                    'mrr_by_month' => ['labels', 'data'],
                    'sales_over_time' => ['labels', 'data'],
                    'orders_over_time' => ['labels', 'data'],
                    'order_status_distribution' => ['labels', 'data'],
                ],
                'vendor_performance',
                'meta' => ['range', 'from', 'to', 'granularity', 'currency', 'vendor_limit'],
            ]);
    }

    public function test_non_admin_user_cannot_access_analytics_endpoint(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER->value]);

        $this->actingAs($customer)
            ->getJson(route('admin.analytics.index'))
            ->assertForbidden();
    }

    public function test_analytics_filters_are_validated_for_custom_range(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);

        $this->actingAs($admin)
            ->getJson(route('admin.analytics.index', ['range' => 'custom', 'to' => '2026-03-25']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['from']);
    }

    public function test_paid_sales_and_mrr_only_include_paid_orders_but_orders_count_tracks_placed_orders(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-25 10:00:00'));

        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER->value]);

        $vendorA = $this->createApprovedVendor('Alpha Store');
        $vendorB = $this->createApprovedVendor('Beta Store');

        // Paid in current month: contributes to MRR and total sales.
        $this->createOrderWithPayment(
            customer: $customer,
            vendor: $vendorA,
            checkoutBatchId: (string) Str::uuid(),
            grandTotal: 150.00,
            orderCreatedAt: Carbon::parse('2026-03-10 10:00:00'),
            paymentStatus: PaymentStatus::PAID,
            paidAt: Carbon::parse('2026-03-10 11:00:00'),
            orderStatus: OrderStatus::CONFIRMED
        );

        // Paid in previous month: contributes to total sales in 12m range and vendor performance.
        $this->createOrderWithPayment(
            customer: $customer,
            vendor: $vendorB,
            checkoutBatchId: (string) Str::uuid(),
            grandTotal: 75.00,
            orderCreatedAt: Carbon::parse('2026-02-16 09:00:00'),
            paymentStatus: PaymentStatus::PAID,
            paidAt: Carbon::parse('2026-02-16 09:30:00'),
            orderStatus: OrderStatus::DELIVERED
        );

        // Unpaid order: should not impact paid revenue/MRR/vendor paid sales, but counts in orders placed.
        $this->createOrderWithPayment(
            customer: $customer,
            vendor: $vendorA,
            checkoutBatchId: (string) Str::uuid(),
            grandTotal: 40.00,
            orderCreatedAt: Carbon::parse('2026-03-20 12:00:00'),
            paymentStatus: PaymentStatus::PENDING,
            paidAt: null,
            orderStatus: OrderStatus::PENDING
        );

        $response = $this->actingAs($admin)->getJson(route('admin.analytics.index', ['range' => '12m']));
        $response->assertOk();

        $json = $response->json();

        $this->assertSame(150.0, (float) data_get($json, 'kpis.mrr'));
        $this->assertSame(225.0, (float) data_get($json, 'kpis.total_sales'));
        $this->assertSame(3, (int) data_get($json, 'kpis.orders_count'));

        $vendors = data_get($json, 'vendor_performance', []);
        $this->assertCount(2, $vendors);
        $this->assertSame('Alpha Store', data_get($vendors, '0.store_name'));
        $this->assertSame(150.0, (float) data_get($vendors, '0.total_sales'));
        $this->assertSame('Beta Store', data_get($vendors, '1.store_name'));
        $this->assertSame(75.0, (float) data_get($vendors, '1.total_sales'));
    }

    private function createApprovedVendor(string $storeName): Vendor
    {
        $owner = User::factory()->create(['role' => UserRole::VENDOR->value]);

        $vendor = Vendor::factory()->create([
            'user_id' => $owner->id,
            'store_name' => $storeName,
            'store_slug' => Str::slug($storeName).'-'.Str::lower(Str::random(6)),
        ]);

        $owner->forceFill(['vendor_id' => $vendor->id])->save();

        return $vendor;
    }

    private function createOrderWithPayment(
        User $customer,
        Vendor $vendor,
        string $checkoutBatchId,
        float $grandTotal,
        Carbon $orderCreatedAt,
        PaymentStatus $paymentStatus,
        ?Carbon $paidAt,
        OrderStatus $orderStatus,
    ): void {
        Order::query()->create([
            'checkout_batch_id' => $checkoutBatchId,
            'order_number' => 'ORD-'.Str::upper(Str::random(10)),
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'payment_provider' => PaymentProvider::STRIPE->value,
            'payment_reference' => 'pay_'.Str::lower(Str::random(12)),
            'payment_status' => $paymentStatus->value,
            'status' => $orderStatus->value,
            'currency' => 'USD',
            'subtotal' => $grandTotal,
            'shipping_amount' => 0,
            'grand_total' => $grandTotal,
            'shipping_address_snapshot' => ['line1' => 'Street 1'],
            'billing_address_snapshot' => ['line1' => 'Street 1'],
            'created_at' => $orderCreatedAt,
            'updated_at' => $orderCreatedAt,
        ]);

        Payment::query()->create([
            'checkout_batch_id' => $checkoutBatchId,
            'user_id' => $customer->id,
            'provider' => PaymentProvider::STRIPE->value,
            'status' => $paymentStatus->value,
            'currency' => 'USD',
            'amount' => $grandTotal,
            'gateway_reference' => 'gw_'.Str::lower(Str::random(12)),
            'stripe_session_id' => 'cs_'.Str::lower(Str::random(12)),
            'stripe_payment_intent_id' => 'pi_'.Str::lower(Str::random(12)),
            'paid_at' => $paidAt,
            'created_at' => $orderCreatedAt,
            'updated_at' => $orderCreatedAt,
        ]);
    }
}
