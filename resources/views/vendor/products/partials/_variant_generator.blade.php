@php
    $attributePayload = collect($attributeOptions ?? [])->map(function ($attribute) {
        return [
            'id' => $attribute->id,
            'name' => $attribute->name,
            'slug' => $attribute->slug,
            'values' => $attribute->values->map(fn ($value) => [
                'id' => $value->id,
                'value' => $value->value,
                'label' => $value->label,
                'code' => $value->code,
            ])->values()->all(),
        ];
    })->values();

    $existingVariantPayload = collect(old('variants', $existingVariants ?? []))->map(function ($variant) {
        $status = is_object($variant['status'] ?? null) ? $variant['status']->value : ($variant['status'] ?? 'active');

        return [
            'combination_key' => $variant['combination_key'] ?? '',
            'sku' => $variant['sku'] ?? '',
            'price' => $variant['price'] ?? '0.00',
            'stock_qty' => $variant['stock_qty'] ?? 0,
            'status' => $status,
            'option_snapshot' => $variant['option_snapshot'] ?? [],
            'values' => $variant['values'] ?? [],
        ];
    })->values();
@endphp

<div
    class="js-variant-generator mt-6 rounded-lg border border-gray-200 bg-white p-5"
    data-generate-url="{{ route('vendor.products.variants.generate') }}"
    data-base-sku-selector="#sku"
    data-attributes='@json($attributePayload)'
    data-existing-variants='@json($existingVariantPayload)'
>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Variant Generator</h3>
            <p class="mt-1 text-sm text-gray-600">Select attribute values to generate SKU, price, and stock rows per combination.</p>
        </div>
        <a href="{{ route('vendor.attributes.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Manage Attributes</a>
    </div>

    <div class="js-attribute-list mt-4 grid gap-4 md:grid-cols-2"></div>

    <div class="mt-4 flex items-center gap-3">
        <button type="button" class="js-generate-variants rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">
            Generate Variants
        </button>
        <p class="js-generate-message text-sm text-gray-500"></p>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Combination</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">SKU</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Price</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Stock</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                </tr>
            </thead>
            <tbody class="js-variant-rows divide-y divide-gray-100 bg-white"></tbody>
        </table>
    </div>
</div>
