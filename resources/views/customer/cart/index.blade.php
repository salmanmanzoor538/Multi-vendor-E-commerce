@extends('layouts.master', ['title' => 'My Cart'])

@section('content')
    @include('partials._alerts')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">My Cart</h1>
            <p class="mt-1 text-sm text-gray-600">Cart is grouped by vendor for split checkout.</p>
        </div>
        <a href="{{ route('customer.checkout.summary') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Proceed to Checkout</a>
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="mb-3 text-base font-semibold">Quick Add Item</h2>
        <form method="POST" action="{{ route('customer.cart.store') }}" class="grid gap-3 md:grid-cols-4">
            @csrf
            <input type="number" min="1" name="product_id" class="rounded-md border-gray-300 text-sm" placeholder="Product ID" required>
            <input type="number" min="1" name="product_variant_id" class="rounded-md border-gray-300 text-sm" placeholder="Variant ID (optional)">
            <input type="number" min="{{ config('cart.quantity.min', 1) }}" max="{{ config('cart.quantity.max', 99) }}" name="quantity" value="1" class="rounded-md border-gray-300 text-sm" required>
            <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Add To Cart</button>
        </form>
    </div>

    @if ($summary['is_empty'])
        <div class="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-600">
            Your cart is empty.
        </div>
    @else
        <div class="space-y-6">
            @foreach ($summary['groups'] as $group)
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
                        <h2 class="text-lg font-semibold">{{ $group['vendor']?->store_name ?? 'Vendor #'.$group['vendor_id'] }}</h2>
                        <p class="text-sm text-gray-600">Subtotal: <span class="font-semibold">{{ number_format($group['subtotal'], 2) }}</span></p>
                    </div>

                    <div class="space-y-3">
                        @foreach ($group['items'] as $item)
                            <div class="rounded-md border border-gray-100 p-4">
                                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $item->product?->name ?? 'Product #'.$item->product_id }}</p>
                                        <p class="text-xs text-gray-500">SKU: {{ $item->productVariant?->sku ?? $item->product?->sku }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('customer.cart.update', $item) }}" class="flex items-center gap-2" data-update-qty-form>
                                        @csrf
                                        @method('PATCH')
                                        <input
                                            type="number"
                                            name="quantity"
                                            min="{{ config('cart.quantity.min', 1) }}"
                                            max="{{ config('cart.quantity.max', 99) }}"
                                            value="{{ $item->quantity }}"
                                            class="w-20 rounded-md border-gray-300 text-sm"
                                        >
                                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700">Update</button>
                                    </form>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500">Unit: {{ number_format((float) $item->unit_price, 2) }}</p>
                                        <p class="text-sm font-semibold text-gray-900">Line: {{ number_format((float) $item->line_total, 2) }}</p>
                                        <form method="POST" action="{{ route('customer.cart.destroy', $item) }}" class="mt-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-700">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between text-sm text-gray-700">
                <span>Subtotal</span>
                <span class="font-medium">{{ number_format($summary['subtotal'], 2) }}</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-sm text-gray-700">
                <span>Shipping</span>
                <span class="font-medium">{{ number_format($summary['shipping_amount'], 2) }}</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-base font-semibold text-gray-900">
                <span>Grand Total</span>
                <span>{{ number_format($summary['grand_total'], 2) }}</span>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    (function ($) {
        if (!$) return;
        $(document).on('change', '[data-update-qty-form] input[name="quantity"]', function () {
            $(this).closest('form').trigger('submit');
        });
    })(window.jQuery || window.$);
</script>
@endpush
