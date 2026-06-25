<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\ProductStatus;
use App\Enums\ProductVariantStatus;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Repositories\Contracts\CartItemRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Services\Cart\Stores\CartStoreInterface;
use App\Services\Cart\Stores\DatabaseCartStore;
use App\Services\Cart\Stores\SessionCartStore;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CartService
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartItemRepositoryInterface $cartItemRepository,
        private readonly SessionCartStore $sessionCartStore,
    ) {
    }

    /**
     * For legacy shop/cart endpoints.
     */
    public function getCart(?User $user): object
    {
        $items = $this->resolveStore($user)->all();

        return $this->toSnapshotObject($items);
    }

    /**
     * For legacy shop/cart endpoints.
     *
     * @param array<string, mixed> $data
     */
    public function addItem(?User $user, array $data): object
    {
        $store = $this->resolveStore($user);
        $product = Product::query()
            ->with('vendor:id,store_name')
            ->findOrFail((int) $data['product_id']);
        abort_if($product->status !== ProductStatus::PUBLISHED, 422, 'Product is not available for checkout.');

        $variant = null;
        if (! empty($data['product_variant_id'])) {
            $variant = ProductVariant::query()->findOrFail((int) $data['product_variant_id']);
            abort_unless((int) $variant->product_id === (int) $product->id, 422, 'Invalid variant selected.');
            abort_if($variant->status !== ProductVariantStatus::ACTIVE, 422, 'Product variant is not available.');
        }

        $lineKey = $this->buildLineKey($product->id, $variant?->id);
        $minQty = (int) config('cart.quantity.min', 1);
        $maxQty = (int) config('cart.quantity.max', 99);
        $requestedQty = max($minQty, min($maxQty, (int) ($data['quantity'] ?? 1)));
        $unitPrice = (float) ($variant?->price ?? $product->price);

        $items = collect($store->all())->keyBy('line_key');
        $existing = $items->get($lineKey);
        $nextQuantity = $existing ? max($minQty, min($maxQty, (int) $existing['quantity'] + $requestedQty)) : $requestedQty;

        $store->put($lineKey, [
            'line_key' => $lineKey,
            'vendor_id' => (int) $product->vendor_id,
            'vendor_name' => (string) ($product->vendor?->store_name ?? ''),
            'product_id' => (int) $product->id,
            'product_variant_id' => $variant?->id,
            'quantity' => $nextQuantity,
            'unit_price' => $this->money($unitPrice),
            'line_total' => $this->money($nextQuantity * $unitPrice),
            'name_snapshot' => (string) $product->name,
            'sku_snapshot' => (string) ($variant?->sku ?? $product->sku),
            'variant_snapshot' => $variant?->option_snapshot,
        ]);

        return $this->getCart($user);
    }

    /**
     * For legacy shop/cart endpoints.
     */
    public function updateItemQuantity(?User $user, string $lineKey, int $quantity): object
    {
        $store = $this->resolveStore($user);
        $items = collect($store->all())->keyBy('line_key');
        $current = $items->get($lineKey);
        abort_unless(is_array($current), 404, 'Cart item not found.');

        $minQty = (int) config('cart.quantity.min', 1);
        $maxQty = (int) config('cart.quantity.max', 99);
        $nextQuantity = max($minQty, min($maxQty, $quantity));
        $unit = (float) ($current['unit_price'] ?? 0);
        $current['quantity'] = $nextQuantity;
        $current['line_total'] = $this->money($nextQuantity * $unit);

        $store->put($lineKey, $current);

        return $this->getCart($user);
    }

    /**
     * For legacy shop/cart endpoints.
     */
    public function removeItem(?User $user, string $lineKey): object
    {
        $store = $this->resolveStore($user);
        $store->remove($lineKey);

        return $this->getCart($user);
    }

    /**
     * For checkout module.
     */
    public function listForUser(User $user): Collection
    {
        return $this->cartItemRepository->getForUser($user->id);
    }

    /**
     * For checkout module customer cart management.
     */
    public function updateQuantity(User $user, CartItem $cartItem, int $quantity): CartItem
    {
        abort_unless((int) $cartItem->user_id === (int) $user->id, 403);

        $minQty = (int) config('cart.quantity.min', 1);
        $maxQty = (int) config('cart.quantity.max', 99);
        $qty = max($minQty, min($maxQty, $quantity));
        $lineTotal = round((float) $cartItem->unit_price * $qty, 2);

        return $this->cartItemRepository->update($cartItem, [
            'quantity' => $qty,
            'line_total' => $lineTotal,
        ]);
    }

    /**
     * For checkout module customer cart management.
     */
    public function removeManagedItem(User $user, CartItem $cartItem): bool
    {
        abort_unless((int) $cartItem->user_id === (int) $user->id, 403);

        return $this->cartItemRepository->delete($cartItem);
    }

    public function clear(User $user): void
    {
        $this->cartItemRepository->clearForUser($user->id);
    }

    public function mergeSessionIntoUserCart(User $user): void
    {
        if (! $user->hasRole(UserRole::CUSTOMER)) {
            return;
        }

        $sessionItems = $this->sessionCartStore->all();
        if ($sessionItems === []) {
            return;
        }

        foreach ($sessionItems as $item) {
            $this->addItem($user, [
                'product_id' => (int) ($item['product_id'] ?? 0),
                'product_variant_id' => $item['product_variant_id'] ?? null,
                'quantity' => (int) ($item['quantity'] ?? 1),
            ]);
        }

        $this->sessionCartStore->clear();
    }

    private function resolveStore(?User $user): CartStoreInterface
    {
        if (! $user) {
            return $this->sessionCartStore;
        }

        $cart = $this->cartRepository->findOrCreateByUserId($user->id);

        return new DatabaseCartStore($cart, $this->cartItemRepository);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function toSnapshotObject(array $items): object
    {
        $groups = collect($items)
            ->groupBy('vendor_id')
            ->map(function ($vendorItems, $vendorId): array {
                $first = $vendorItems->first();

                return [
                    'vendor_id' => (int) $vendorId,
                    'vendor_name' => (string) ($first['vendor_name'] ?? 'Vendor #'.$vendorId),
                    'subtotal' => $this->money((float) $vendorItems->sum(fn ($item) => (float) $item['line_total'])),
                    'items' => $vendorItems->values()->all(),
                ];
            })
            ->values()
            ->all();

        $itemsCount = (int) collect($items)->sum('quantity');
        $grandTotal = $this->money((float) collect($items)->sum(fn ($item) => (float) $item['line_total']));
        $snapshot = [
            'groups' => $groups,
            'totals' => [
                'items_count' => $itemsCount,
                'grand_total' => $grandTotal,
            ],
        ];

        return new class($snapshot)
        {
            /**
             * @param array<string, mixed> $snapshot
             */
            public function __construct(private readonly array $snapshot)
            {
            }

            /**
             * @return array<string, mixed>
             */
            public function toArray(): array
            {
                return $this->snapshot;
            }
        };
    }

    private function buildLineKey(int $productId, ?int $variantId): string
    {
        $raw = $variantId ? "product:{$productId}|variant:{$variantId}" : "product:{$productId}|variant:none";

        return Str::of($raw)->replace('|', '-')->replace(':', '-')->value();
    }

    private function money(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
