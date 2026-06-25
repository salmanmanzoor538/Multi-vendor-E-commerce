<?php

namespace App\Repositories\Contracts;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;

interface CartItemRepositoryInterface
{
    public function getForCart(Cart $cart): Collection;

    public function findByLineKey(Cart $cart, string $lineKey): ?CartItem;

    public function getForUser(int $userId): Collection;

    public function findForUser(int $userId, int $cartItemId): ?CartItem;

    public function findByUserAndItemKey(int $userId, string $itemKey): ?CartItem;

    /**
     * @param Cart|array<string, mixed> $cartOrData
     * @param array<string, mixed> $data
     */
    public function create(Cart|array $cartOrData, array $data = []): CartItem;

    public function update(CartItem $cartItem, array $data): CartItem;

    public function delete(CartItem $cartItem): bool;

    public function deleteAllForCart(Cart $cart): void;

    public function clearForUser(int $userId): void;
}
