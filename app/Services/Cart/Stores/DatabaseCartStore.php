<?php

namespace App\Services\Cart\Stores;

use App\Models\Cart;
use App\Repositories\Contracts\CartItemRepositoryInterface;
use Illuminate\Support\Arr;

class DatabaseCartStore implements CartStoreInterface
{
    public function __construct(
        private readonly Cart $cart,
        private readonly CartItemRepositoryInterface $cartItemRepository,
    ) {
    }

    public function all(): array
    {
        return $this->cartItemRepository
            ->getForCart($this->cart)
            ->map(fn ($item) => [
                'line_key' => $item->line_key,
                'vendor_id' => (int) $item->vendor_id,
                'vendor_name' => (string) ($item->vendor?->store_name ?? ''),
                'product_id' => (int) $item->product_id,
                'product_variant_id' => $item->product_variant_id ? (int) $item->product_variant_id : null,
                'quantity' => (int) $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'line_total' => (string) $item->line_total,
                'name_snapshot' => $item->name_snapshot,
                'sku_snapshot' => $item->sku_snapshot,
                'variant_snapshot' => $item->variant_snapshot,
            ])
            ->values()
            ->all();
    }

    public function put(string $lineKey, array $item): void
    {
        $persistable = Arr::only($item, [
            'line_key',
            'vendor_id',
            'product_id',
            'product_variant_id',
            'quantity',
            'unit_price',
            'line_total',
            'name_snapshot',
            'sku_snapshot',
            'variant_snapshot',
        ]);

        $existing = $this->cartItemRepository->findByLineKey($this->cart, $lineKey);

        if ($existing) {
            $this->cartItemRepository->update($existing, $persistable);

            return;
        }

        $this->cartItemRepository->create($this->cart, $persistable);
    }

    public function remove(string $lineKey): void
    {
        $existing = $this->cartItemRepository->findByLineKey($this->cart, $lineKey);

        if (! $existing) {
            return;
        }

        $this->cartItemRepository->delete($existing);
    }

    public function clear(): void
    {
        $this->cartItemRepository->deleteAllForCart($this->cart);
    }
}
