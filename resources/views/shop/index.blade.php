@extends('layouts.master', ['title' => 'Shop'])

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Shop</h1>
            <p class="mt-1 text-sm text-gray-600">Browse published products and add items to your cart.</p>
        </div>
        <a href="{{ route('cart.index') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">View Cart</a>
    </div>

    <div class="mb-4 hidden rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" data-cart-feedback></div>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($products as $product)
            <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900">{{ $product->name }}</h2>
                <p class="mt-1 text-xs text-gray-500">{{ $product->vendor?->store_name }}</p>

                @if ($product->description)
                    <p class="mt-3 line-clamp-3 text-sm text-gray-600">{{ $product->description }}</p>
                @endif

                <div class="mt-4 text-sm text-gray-700">
                    Base price: <span class="font-semibold">{{ number_format((float) $product->price, 2) }}</span>
                </div>
                <div class="mt-1 text-xs text-gray-600">
                    @if ((int) ($product->approved_reviews_count ?? 0) > 0)
                        Rating: {{ number_format((float) $product->approved_reviews_avg_rating, 1) }}/5
                        ({{ $product->approved_reviews_count }} reviews)
                    @else
                        No reviews yet
                    @endif
                </div>

                <form class="mt-4 space-y-3" data-cart-add-form>
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    @if ($product->variants->isNotEmpty())
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700">Variant</label>
                            <select name="product_variant_id" class="w-full rounded-md border-gray-300 text-sm">
                                <option value="">Base product</option>
                                @foreach ($product->variants as $variant)
                                    <option value="{{ $variant->id }}">
                                        {{ $variant->sku }} - {{ number_format((float) $variant->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Quantity</label>
                        <input
                            type="number"
                            name="quantity"
                            value="1"
                            min="{{ config('cart.quantity.min', 1) }}"
                            max="{{ config('cart.quantity.max', 99) }}"
                            class="w-full rounded-md border-gray-300 text-sm"
                        >
                    </div>

                    <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Add to Cart
                    </button>
                </form>
            </article>
        @empty
            <div class="col-span-full rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-600">
                No published products are available right now.
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $products->links() }}
    </div>

    <div
        id="shop-cart-config"
        data-add-url="{{ route('cart.items.store') }}"
        data-summary-url="{{ route('cart.summary') }}"
    ></div>
@endsection
