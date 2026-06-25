<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\CartItemRepositoryInterface;

class CheckoutService
{
    public function __construct(private readonly CartItemRepositoryInterface $cartItemRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSummary(User $user): array
    {
        $cartItems = $this->cartItemRepository->getForUser($user->id);
        $flatShipping = (float) config('checkout.shipping.flat_per_vendor', 0);
        $currency = strtoupper((string) config('checkout.currency', 'usd'));
        $groups = [];
        $grandSubtotal = 0.0;
        $grandShipping = 0.0;

        foreach ($cartItems->groupBy('vendor_id') as $vendorId => $vendorItems) {
            $vendorSubtotal = (float) $vendorItems->sum('line_total');
            $shipping = $vendorSubtotal > 0 ? $flatShipping : 0;
            $vendorTotal = $vendorSubtotal + $shipping;

            $groups[] = [
                'vendor_id' => (int) $vendorId,
                'vendor' => $vendorItems->first()?->vendor,
                'items' => $vendorItems->values(),
                'subtotal' => round($vendorSubtotal, 2),
                'shipping_amount' => round($shipping, 2),
                'total' => round($vendorTotal, 2),
            ];

            $grandSubtotal += $vendorSubtotal;
            $grandShipping += $shipping;
        }

        return [
            'groups' => $groups,
            'currency' => $currency,
            'subtotal' => round($grandSubtotal, 2),
            'shipping_amount' => round($grandShipping, 2),
            'grand_total' => round($grandSubtotal + $grandShipping, 2),
            'is_empty' => count($groups) === 0,
        ];
    }
}
