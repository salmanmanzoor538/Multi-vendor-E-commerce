@extends('layouts.master', ['title' => 'Cart'])

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Your Cart</h1>
            <p class="mt-1 text-sm text-gray-600">Items are grouped by vendor with real-time price recalculation.</p>
        </div>
        <a href="{{ route('shop.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Continue Shopping</a>
    </div>

    <div class="mb-4 hidden rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" data-cart-error></div>

    <div
        id="cart-root"
        data-summary-url="{{ route('cart.summary') }}"
        data-update-url-template="{{ route('cart.items.update', ['lineKey' => '__LINE_KEY__']) }}"
        data-remove-url-template="{{ route('cart.items.destroy', ['lineKey' => '__LINE_KEY__']) }}"
        data-min-qty="{{ config('cart.quantity.min', 1) }}"
        data-max-qty="{{ config('cart.quantity.max', 99) }}"
        data-cart='@json($cart)'
    >
        @php
            $groups = $cart['groups'] ?? [];
            $totals = $cart['totals'] ?? ['items_count' => 0, 'grand_total' => '0.00'];
        @endphp

        <div class="space-y-6" data-cart-groups>
            @forelse ($groups as $group)
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm" data-vendor-group="{{ $group['vendor_id'] }}">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
                        <h2 class="text-lg font-semibold">{{ $group['vendor_name'] }}</h2>
                        <p class="text-sm text-gray-600">Subtotal: <span class="font-semibold">{{ $group['subtotal'] }}</span></p>
                    </div>

                    <div class="space-y-3">
                        @foreach ($group['items'] as $item)
                            <div class="flex flex-col gap-3 rounded-md border border-gray-100 p-4 md:flex-row md:items-center md:justify-between" data-line-key="{{ $item['line_key'] }}">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $item['name_snapshot'] }}</p>
                                    <p class="text-xs text-gray-500">SKU: {{ $item['sku_snapshot'] }}</p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <input
                                        type="number"
                                        class="w-20 rounded-md border-gray-300 text-sm"
                                        min="{{ config('cart.quantity.min', 1) }}"
                                        max="{{ config('cart.quantity.max', 99) }}"
                                        value="{{ $item['quantity'] }}"
                                        data-item-quantity
                                    >
                                    <button type="button" class="rounded-md bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700" data-update-item>
                                        Update
                                    </button>
                                    <button type="button" class="rounded-md border border-red-300 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50" data-remove-item>
                                        Remove
                                    </button>
                                </div>

                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Unit: {{ $item['unit_price'] }}</p>
                                    <p class="text-sm font-semibold text-gray-900">Line: {{ $item['line_total'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-600" data-empty-state>
                    Your cart is empty.
                </div>
            @endforelse
        </div>

        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm" data-cart-totals>
            <div class="flex items-center justify-between text-sm text-gray-700">
                <span>Total Items</span>
                <span class="font-medium" data-items-count>{{ $totals['items_count'] }}</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-base font-semibold text-gray-900">
                <span>Grand Total</span>
                <span data-grand-total>{{ $totals['grand_total'] }}</span>
            </div>
        </div>
    </div>
@endsection
