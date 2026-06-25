<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\Contracts\CartItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CartItemRepository implements CartItemRepositoryInterface
{
    public function getForCart(Cart $cart): Collection
    {
        return CartItem::query()
            ->with([
                'vendor:id,store_name',
                'product:id,name,sku',
                'productVariant:id,sku,option_snapshot',
            ])
            ->where('cart_id', $cart->id)
            ->orderBy('id')
            ->get();
    }

    public function findByLineKey(Cart $cart, string $lineKey): ?CartItem
    {
        return CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('line_key', $lineKey)
            ->first();
    }

    public function getForUser(int $userId): Collection
    {
        return CartItem::query()
            ->with([
                'vendor:id,store_name',
                'product:id,name,sku',
                'productVariant:id,sku,option_snapshot',
            ])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function findForUser(int $userId, int $cartItemId): ?CartItem
    {
        return CartItem::query()
            ->where('user_id', $userId)
            ->whereKey($cartItemId)
            ->first();
    }

    public function findByUserAndItemKey(int $userId, string $itemKey): ?CartItem
    {
        return CartItem::query()
            ->where('user_id', $userId)
            ->where('item_key', $itemKey)
            ->first();
    }

    public function create(Cart|array $cartOrData, array $data = []): CartItem
    {
        if ($cartOrData instanceof Cart) {
            return CartItem::query()->create([
                ...$data,
                'cart_id' => $cartOrData->id,
                'user_id' => $cartOrData->user_id,
                'item_key' => (string) ($data['line_key'] ?? ''),
                'currency' => strtoupper((string) config('checkout.currency', 'usd')),
            ]);
        }

        return CartItem::query()->create($cartOrData);
    }

    public function update(CartItem $cartItem, array $data): CartItem
    {
        $cartItem->fill($data)->save();

        return $cartItem->fresh(['vendor', 'product', 'productVariant']);
    }

    public function delete(CartItem $cartItem): bool
    {
        return (bool) $cartItem->delete();
    }

    public function clearForUser(int $userId): void
    {
        CartItem::query()->where('user_id', $userId)->delete();
    }

    public function deleteAllForCart(Cart $cart): void
    {
        CartItem::query()->where('cart_id', $cart->id)->delete();
    }
}
