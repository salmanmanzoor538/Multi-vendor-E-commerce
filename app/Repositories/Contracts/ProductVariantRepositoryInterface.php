<?php

namespace App\Repositories\Contracts;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;

interface ProductVariantRepositoryInterface
{
    /**
     * @return Collection<int, ProductVariant>
     */
    public function getForProduct(int $productId): Collection;

    /**
     * @return Collection<int, ProductVariant>
     */
    public function getForProductWithoutValues(int $productId): Collection;

    public function findByProductAndCombinationKey(int $productId, string $combinationKey): ?ProductVariant;

    public function create(array $data): ProductVariant;

    public function update(ProductVariant $variant, array $data): ProductVariant;

    public function deleteValues(ProductVariant $variant): void;

    public function createValue(array $data): void;

    public function deleteValuesByVariantIds(array $variantIds): void;

    public function createValuesBulk(array $rows): void;

    /**
     * @param array<int, string> $combinationKeys
     * @return Collection<int, ProductVariant>
     */
    public function findManyByProductAndCombinationKeys(int $productId, array $combinationKeys): Collection;

    public function deactivateMissingCombinations(int $productId, array $combinationKeys): void;

    public function skuExistsForVendor(int $vendorId, string $sku, ?int $exceptVariantId = null): bool;
}
