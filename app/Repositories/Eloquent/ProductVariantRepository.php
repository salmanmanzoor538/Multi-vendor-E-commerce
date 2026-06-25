<?php

namespace App\Repositories\Eloquent;

use App\Enums\ProductVariantStatus;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use Illuminate\Support\Collection;

class ProductVariantRepository implements ProductVariantRepositoryInterface
{
    public function getForProduct(int $productId): Collection
    {
        return ProductVariant::query()
            ->with('values')
            ->where('product_id', $productId)
            ->orderBy('id')
            ->get();
    }

    public function getForProductWithoutValues(int $productId): Collection
    {
        return ProductVariant::query()
            ->where('product_id', $productId)
            ->orderBy('id')
            ->get();
    }

    public function findByProductAndCombinationKey(int $productId, string $combinationKey): ?ProductVariant
    {
        return ProductVariant::query()
            ->with('values')
            ->where('product_id', $productId)
            ->where('combination_key', $combinationKey)
            ->first();
    }

    public function create(array $data): ProductVariant
    {
        return ProductVariant::query()->create($data);
    }

    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->fill($data)->save();

        return $variant->fresh();
    }

    public function deleteValues(ProductVariant $variant): void
    {
        ProductVariantValue::query()->where('product_variant_id', $variant->id)->delete();
    }

    public function createValue(array $data): void
    {
        ProductVariantValue::query()->create($data);
    }

    public function deleteValuesByVariantIds(array $variantIds): void
    {
        if ($variantIds === []) {
            return;
        }

        ProductVariantValue::query()
            ->whereIn('product_variant_id', $variantIds)
            ->delete();
    }

    public function createValuesBulk(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $timestamp = now();
        $rows = array_map(static function (array $row) use ($timestamp): array {
            return [
                ...$row,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $rows);

        ProductVariantValue::query()->insert($rows);
    }

    public function findManyByProductAndCombinationKeys(int $productId, array $combinationKeys): Collection
    {
        if ($combinationKeys === []) {
            return collect();
        }

        return ProductVariant::query()
            ->with('values')
            ->where('product_id', $productId)
            ->whereIn('combination_key', $combinationKeys)
            ->get();
    }

    public function deactivateMissingCombinations(int $productId, array $combinationKeys): void
    {
        ProductVariant::query()
            ->where('product_id', $productId)
            ->whereNotIn('combination_key', $combinationKeys)
            ->update(['status' => ProductVariantStatus::INACTIVE->value]);
    }

    public function skuExistsForVendor(int $vendorId, string $sku, ?int $exceptVariantId = null): bool
    {
        return ProductVariant::query()
            ->where('vendor_id', $vendorId)
            ->where('sku', $sku)
            ->when($exceptVariantId, fn ($query) => $query->where('id', '!=', $exceptVariantId))
            ->exists();
    }
}
