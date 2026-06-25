@extends('layouts.master', ['title' => $product->name])

@section('content')
    @include('partials._alerts')

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">{{ $product->name }}</h1>
                <p class="mt-1 text-sm text-gray-600">SKU: {{ $product->sku }}</p>
            </div>
            <a href="{{ route('vendor.products.edit', $product) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Edit</a>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="rounded-md border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Price</p>
                <p class="text-lg font-semibold">{{ number_format((float) $product->price, 2) }}</p>
            </div>
            <div class="rounded-md border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Stock</p>
                <p class="text-lg font-semibold">{{ $product->stock }}</p>
            </div>
            <div class="rounded-md border border-gray-200 p-4 md:col-span-2">
                <p class="text-sm text-gray-500">Status</p>
                <p class="text-lg font-semibold">{{ ucfirst($product->status?->value ?? 'draft') }}</p>
            </div>
        </div>

        @if ($product->description)
            <div class="mt-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Description</h2>
                <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $product->description }}</p>
            </div>
        @endif

        <div class="mt-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Categories</h2>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($product->categories as $category)
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs text-indigo-700">{{ $category->name }}</span>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Images</h2>
            @if ($product->images->isNotEmpty())
                <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($product->images as $image)
                        <div class="rounded-md border border-gray-200 p-2">
                            <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $image->alt_text }}" class="h-36 w-full rounded object-cover">
                            @if ($image->is_primary)
                                <span class="mt-2 inline-block rounded bg-emerald-100 px-2 py-1 text-xs text-emerald-700">Primary</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-2 text-sm text-gray-500">No images uploaded.</p>
            @endif
        </div>
    </div>
@endsection
