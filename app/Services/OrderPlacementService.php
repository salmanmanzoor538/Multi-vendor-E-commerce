<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Events\OrdersPlaced;
use App\Models\CustomerAddress;
use App\Models\User;
use App\Repositories\Contracts\CartItemRepositoryInterface;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Support\PerformanceCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderPlacementService
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly CartItemRepositoryInterface $cartItemRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderItemRepositoryInterface $orderItemRepository,
        private readonly OrderManagementService $orderManagementService,
    ) {
    }

    /**
     * @return array{checkout_batch_id: string, orders: Collection<int, \App\Models\Order>, grand_total: float, currency: string}
     */
    public function place(
        User $user,
        CustomerAddress $shippingAddress,
        CustomerAddress $billingAddress,
        PaymentProvider $paymentProvider,
    ): array {
        $summary = $this->checkoutService->buildSummary($user);
        abort_if($summary['is_empty'], 422, 'Cannot checkout with an empty cart.');

        $result = DB::transaction(function () use ($user, $summary, $shippingAddress, $billingAddress, $paymentProvider): array {
            $checkoutBatchId = (string) Str::uuid();
            $orders = collect();

            foreach ($summary['groups'] as $group) {
                $order = $this->orderRepository->create([
                    'checkout_batch_id' => $checkoutBatchId,
                    'order_number' => $this->nextOrderNumber(),
                    'user_id' => $user->id,
                    'vendor_id' => $group['vendor_id'],
                    'payment_provider' => $paymentProvider->value,
                    'payment_reference' => null,
                    'payment_status' => PaymentStatus::PENDING->value,
                    'status' => OrderStatus::PENDING->value,
                    'currency' => $summary['currency'],
                    'subtotal' => $group['subtotal'],
                    'shipping_amount' => $group['shipping_amount'],
                    'grand_total' => $group['total'],
                    'shipping_address_snapshot' => $this->addressSnapshot($shippingAddress),
                    'billing_address_snapshot' => $this->addressSnapshot($billingAddress),
                ]);

                $rows = [];
                foreach ($group['items'] as $item) {
                    $rows[] = [
                        'vendor_id' => $group['vendor_id'],
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'product_name' => $item->product?->name ?? 'Product',
                        'sku_snapshot' => $item->productVariant?->sku ?? $item->product?->sku,
                        'variant_snapshot' => $item->productVariant?->option_snapshot,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'line_total' => $item->line_total,
                        'currency' => $summary['currency'],
                    ];
                }

                $this->orderItemRepository->createMany($order, $rows);
                $this->orderManagementService->createTimelineEntry($order, OrderStatus::PENDING, $user, 'Order placed.');
                $orders->push($order);
            }

            $this->cartItemRepository->clearForUser($user->id);

            return [
                'checkout_batch_id' => $checkoutBatchId,
                'orders' => $orders,
            ];
        });

        PerformanceCache::invalidate((string) config('performance.cache.namespaces.analytics', 'analytics'));

        event(new OrdersPlaced($result['checkout_batch_id'], $result['orders']));

        return [
            ...$result,
            'grand_total' => (float) $summary['grand_total'],
            'currency' => $summary['currency'],
        ];
    }

    /**
     * @return array<string, string|bool>
     */
    private function addressSnapshot(CustomerAddress $address): array
    {
        return [
            'type' => $address->type->value,
            'full_name' => $address->full_name,
            'phone' => $address->phone,
            'line_1' => $address->line_1,
            'line_2' => (string) ($address->line_2 ?? ''),
            'city' => $address->city,
            'state' => $address->state,
            'postal_code' => $address->postal_code,
            'country_code' => $address->country_code,
            'is_default' => $address->is_default,
        ];
    }

    private function nextOrderNumber(): string
    {
        $prefix = (string) config('checkout.order.number_prefix', 'ORD');
        $date = now()->format('Ymd');

        do {
            $candidate = sprintf('%s-%s-%s', strtoupper($prefix), $date, strtoupper(Str::random(8)));
        } while ($this->orderRepository->existsByOrderNumber($candidate));

        return $candidate;
    }
}
