<?php

namespace App\Services;

use App\Models\Attribute;
use App\Repositories\Contracts\AttributeRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductVariantGenerationService
{
    public function __construct(private readonly AttributeRepositoryInterface $attributeRepository)
    {
    }

    /**
     * @param array<int, array{attribute_id:int, value_ids:array<int, int>}> $attributeSelections
     * @return array<int, array<string, mixed>>
     */
    public function generateForVendor(int $vendorId, array $attributeSelections, ?string $baseSku = null): array
    {
        $attributeIds = array_values(array_unique(array_map(
            fn (array $item): int => (int) $item['attribute_id'],
            $attributeSelections
        )));

        /** @var Collection<int, Attribute> $attributes */
        $attributes = $this->attributeRepository->findManyWithValuesForVendor($vendorId, $attributeIds);
        $attributesById = $attributes->keyBy('id');

        $groups = [];

        foreach ($attributeSelections as $selection) {
            /** @var Attribute|null $attribute */
            $attribute = $attributesById->get((int) $selection['attribute_id']);
            if (! $attribute) {
                continue;
            }

            $valueIds = array_map('intval', $selection['value_ids']);
            $selectedValues = $attribute->values
                ->whereIn('id', $valueIds)
                ->sortBy('sort_order')
                ->values();

            if ($selectedValues->isEmpty()) {
                continue;
            }

            $groups[] = [
                'attribute' => $attribute,
                'values' => $selectedValues->all(),
            ];
        }

        if ($groups === []) {
            return [];
        }

        $combinations = $this->cartesian($groups);
        $rows = [];

        foreach ($combinations as $combination) {
            $pairs = [];
            $snapshot = [];
            $skuSuffix = [];

            foreach ($combination as $item) {
                $attribute = $item['attribute'];
                $value = $item['value'];

                $pairs[] = $attribute->id.':'.$value->id;
                $snapshot[] = [
                    'attribute_id' => $attribute->id,
                    'attribute_name' => $attribute->name,
                    'attribute_slug' => $attribute->slug,
                    'attribute_value_id' => $value->id,
                    'attribute_value' => $value->value,
                    'attribute_value_label' => $value->label,
                    'attribute_value_code' => $value->code,
                ];
                $skuSuffix[] = $value->code ?: Str::upper(Str::slug($value->value, ''));
            }

            sort($pairs);

            $rows[] = [
                'combination_key' => implode('|', $pairs),
                'option_snapshot' => $snapshot,
                'generated_sku' => $this->buildSku($baseSku, $skuSuffix),
                'status' => 'active',
            ];
        }

        return $rows;
    }

    /**
     * @param array<int, array{attribute:Attribute, values:array<int, mixed>}> $groups
     * @return array<int, array<int, array{attribute:Attribute, value:mixed}>>
     */
    private function cartesian(array $groups): array
    {
        $result = [[]];

        foreach ($groups as $group) {
            $next = [];
            foreach ($result as $partial) {
                foreach ($group['values'] as $value) {
                    $partialWithValue = $partial;
                    $partialWithValue[] = [
                        'attribute' => $group['attribute'],
                        'value' => $value,
                    ];
                    $next[] = $partialWithValue;
                }
            }
            $result = $next;
        }

        return $result;
    }

    /**
     * @param array<int, string> $suffixParts
     */
    public function buildSku(?string $baseSku, array $suffixParts): string
    {
        $prefix = trim((string) $baseSku) !== '' ? strtoupper((string) $baseSku) : 'SKU';
        $suffix = implode('-', array_filter(array_map(
            static fn (string $part): string => strtoupper(trim($part)),
            $suffixParts
        )));

        return $suffix !== '' ? $prefix.'-'.$suffix : $prefix;
    }
}
