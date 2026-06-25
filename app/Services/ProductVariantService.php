<?php

namespace App\Services;

use App\Enums\ProductVariantStatus;
use App\Events\ProductVariantsSynced;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductVariantService
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly ProductVariantGenerationService $generationService,
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $variants
     * @return array<int, ProductVariant>
     */
    public function syncVariants(Product $product, array $variants): array
    {
        $savedVariants = DB::transaction(function () use ($product, $variants): array {
            $combinationKeys = [];
            $savedVariantIds = [];
            $valueRows = [];

            $existingByCombination = $this->productVariantRepository
                ->getForProductWithoutValues($product->id)
                ->keyBy('combination_key');

            foreach ($variants as $variantPayload) {
                $combinationKey = (string) $variantPayload['combination_key'];
                $combinationKeys[] = $combinationKey;

                $existing = $existingByCombination->get($combinationKey);

                $sku = (string) ($variantPayload['sku'] ?? '');
                if ($sku === '') {
                    $codes = collect($variantPayload['option_snapshot'] ?? [])
                        ->map(fn (array $snapshot): string => (string) ($snapshot['attribute_value_code'] ?? ''))
                        ->all();
                    $sku = $this->generationService->buildSku($product->sku ?? null, $codes);
                }

                $sku = $this->resolveUniqueSku($product->vendor_id, $sku, $existing?->id);

                $data = [
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'sku' => $sku,
                    'price' => $variantPayload['price'] ?? ($existing?->price ?? 0),
                    'stock_qty' => $variantPayload['stock_qty'] ?? ($existing?->stock_qty ?? 0),
                    'status' => $variantPayload['status'] ?? ProductVariantStatus::ACTIVE->value,
                    'combination_key' => $combinationKey,
                    'option_snapshot' => $variantPayload['option_snapshot'] ?? ($existing?->option_snapshot ?? []),
                ];

                $variant = $existing
                    ? $this->productVariantRepository->update($existing, $data)
                    : $this->productVariantRepository->create($data);

                $savedVariantIds[] = $variant->id;

                foreach ($variantPayload['values'] ?? [] as $valuePayload) {
                    $valueRows[] = [
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $valuePayload['attribute_id'],
                        'attribute_value_id' => $valuePayload['attribute_value_id'],
                        'vendor_id' => $product->vendor_id,
                    ];
                }

            }

            $this->productVariantRepository->deleteValuesByVariantIds($savedVariantIds);
            $this->productVariantRepository->createValuesBulk($valueRows);
            $this->productVariantRepository->deactivateMissingCombinations($product->id, $combinationKeys);

            $savedWithValues = $this->productVariantRepository
                ->findManyByProductAndCombinationKeys($product->id, $combinationKeys)
                ->keyBy('combination_key');

            return collect($combinationKeys)
                ->map(fn (string $key) => $savedWithValues->get($key))
                ->filter()
                ->values()
                ->all();
        });

        event(new ProductVariantsSynced($product, $savedVariants));

        return $savedVariants;
    }

    /**
     * @return array<int, ProductVariant>
     */
    public function getForProduct(Product $product): array
    {
        return $this->productVariantRepository->getForProduct($product->id)->all();
    }

    private function resolveUniqueSku(int $vendorId, string $preferredSku, ?int $exceptVariantId = null): string
    {
        $candidate = $preferredSku;
        $suffix = 1;

        while ($this->productVariantRepository->skuExistsForVendor($vendorId, $candidate, $exceptVariantId)) {
            $candidate = $preferredSku.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
