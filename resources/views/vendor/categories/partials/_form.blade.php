@php
    $isEdit = isset($category);
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-700" for="name">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $category->name ?? '') }}" required class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="slug">Slug</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $category->slug ?? '') }}" class="mt-1 w-full rounded-md border-gray-300">
        <p class="mt-1 text-xs text-gray-500">Leave blank to auto-generate from name.</p>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700" for="parent_id">Parent Category</label>
        <select id="parent_id" name="parent_id" class="mt-1 w-full rounded-md border-gray-300">
            <option value="">No parent</option>
            @foreach ($categoryOptions as $option)
                @continue($isEdit && $option['id'] === $category->id)
                <option value="{{ $option['id'] }}" @selected((string) old('parent_id', $category->parent_id ?? '') === (string) $option['id'])>
                    {{ $option['name'] }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">
        {{ $isEdit ? 'Update Category' : 'Create Category' }}
    </button>
    <a href="{{ $routePrefix === 'admin' ? route('admin.categories.index') : route('vendor.categories.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
</div>
