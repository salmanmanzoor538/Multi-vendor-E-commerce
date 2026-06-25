@php
    $plan = $plan ?? null;
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $plan?->name) }}" class="mt-1 w-full rounded-md border-gray-300" required>
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium text-gray-700">Slug (optional)</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $plan?->slug) }}" class="mt-1 w-full rounded-md border-gray-300">
        @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="monthly_price" class="block text-sm font-medium text-gray-700">Monthly Price</label>
        <input id="monthly_price" name="monthly_price" type="number" min="0" step="0.01" value="{{ old('monthly_price', $plan?->monthly_price ?? 0) }}" class="mt-1 w-full rounded-md border-gray-300" required>
        @error('monthly_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="yearly_price" class="block text-sm font-medium text-gray-700">Yearly Price</label>
        <input id="yearly_price" name="yearly_price" type="number" min="0" step="0.01" value="{{ old('yearly_price', $plan?->yearly_price ?? 0) }}" class="mt-1 w-full rounded-md border-gray-300" required>
        @error('yearly_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="limits_product_count" class="block text-sm font-medium text-gray-700">Product Limit</label>
        <input id="limits_product_count" name="limits[product_count]" type="number" min="1" value="{{ old('limits.product_count', data_get($plan?->limits, 'product_count')) }}" class="mt-1 w-full rounded-md border-gray-300" placeholder="Leave blank for unlimited">
        @error('limits.product_count')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="limits_storage_mb" class="block text-sm font-medium text-gray-700">Storage Limit (MB)</label>
        <input id="limits_storage_mb" name="limits[storage_mb]" type="number" min="1" value="{{ old('limits.storage_mb', data_get($plan?->limits, 'storage_mb')) }}" class="mt-1 w-full rounded-md border-gray-300" placeholder="Leave blank for unlimited">
        @error('limits.storage_mb')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}" class="mt-1 w-full rounded-md border-gray-300">
        @error('sort_order')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-end">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" @checked((bool) old('is_active', $plan?->is_active ?? true))>
            Active
        </label>
    </div>
</div>

<div class="mt-4">
    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
    <textarea id="description" name="description" rows="4" class="mt-1 w-full rounded-md border-gray-300">{{ old('description', $plan?->description) }}</textarea>
    @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<div class="mt-6 flex items-center gap-4">
    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black">Save Plan</button>
    <a href="{{ route('admin.plans.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
</div>
