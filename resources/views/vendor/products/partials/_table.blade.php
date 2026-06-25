<div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">SKU</th>
                <th class="px-4 py-3">Price</th>
                <th class="px-4 py-3">Stock</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white text-gray-700">
            @forelse ($products as $product)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $product->name }}</td>
                    <td class="px-4 py-3">{{ $product->sku }}</td>
                    <td class="px-4 py-3">{{ number_format((float) $product->price, 2) }}</td>
                    <td class="px-4 py-3">{{ $product->stock }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-1 text-xs {{ $product->status?->value === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ ucfirst($product->status?->value ?? 'draft') }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('vendor.products.show', $product) }}" class="text-gray-600 hover:text-gray-900">View</a>
                            <a href="{{ route('vendor.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                            <form method="POST" action="{{ route('vendor.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
