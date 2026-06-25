@php
    $isEdit = isset($product);
    $selectedCategoryIds = old('category_ids', $isEdit ? $product->categories->pluck('id')->all() : []);
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-700" for="name">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $product->name ?? '') }}" required class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="sku">SKU</label>
        <input id="sku" name="sku" type="text" value="{{ old('sku', $product->sku ?? '') }}" required class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="price">Price</label>
        <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $product->price ?? '0.00') }}" required class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="stock">Stock</label>
        <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="status">Status</label>
        <select id="status" name="status" class="mt-1 w-full rounded-md border-gray-300" required>
            @foreach (\App\Enums\ProductStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $product->status?->value ?? \App\Enums\ProductStatus::DRAFT->value) === $status->value)>
                    {{ ucfirst($status->value) }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="category_ids">Categories</label>
        <select id="category_ids" name="category_ids[]" multiple required class="mt-1 h-40 w-full rounded-md border-gray-300">
            @foreach ($categoryOptions as $categoryOption)
                <option value="{{ $categoryOption['id'] }}" @selected(in_array($categoryOption['id'], $selectedCategoryIds, true))>
                    {{ $categoryOption['name'] }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Use Ctrl/Cmd to select multiple categories.</p>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700" for="description">Description</label>
        <textarea id="description" name="description" rows="4" class="mt-1 w-full rounded-md border-gray-300">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700" for="images">Upload Images</label>
        <input id="images" name="images[]" type="file" multiple accept="image/*" class="mt-1 w-full rounded-md border-gray-300">
    </div>
</div>

@include('vendor.products.partials._variant_generator', [
    'attributeOptions' => $attributeOptions ?? collect(),
    'existingVariants' => $existingVariants ?? [],
])

@if ($isEdit && $product->images->isNotEmpty())
    <div class="mt-6">
        <h3 class="text-sm font-semibold text-gray-900">Existing Images</h3>
        <div class="mt-3 space-y-3">
            @foreach ($product->images as $image)
                <div class="flex items-center gap-3 rounded-md border border-gray-200 bg-gray-50 p-3">
                    <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $image->alt_text }}" class="h-16 w-16 rounded-md object-cover">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="primary_image" value="existing:{{ $image->id }}" @checked(old('primary_image', $product->images->firstWhere('is_primary', true)?->id ? 'existing:'.$product->images->firstWhere('is_primary', true)?->id : null) === 'existing:'.$image->id)>
                        Primary
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-red-700">
                        <input type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}" {{ in_array($image->id, old('remove_image_ids', [])) ? 'checked' : '' }}>
                        Remove
                    </label>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">
        {{ $isEdit ? 'Update Product' : 'Create Product' }}
    </button>
    <a href="{{ route('vendor.products.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
</div>
