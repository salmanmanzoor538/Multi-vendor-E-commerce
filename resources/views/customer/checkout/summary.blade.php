@extends('layouts.master', ['title' => 'Checkout'])

@section('content')
    @include('partials._alerts')

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Checkout Summary</h1>
        <p class="mt-1 text-sm text-gray-600">Order will be split automatically per vendor.</p>
    </div>

    @if ($summary['is_empty'])
        <div class="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-600">
            Your cart is empty. <a href="{{ route('customer.cart.index') }}" class="text-indigo-600 hover:text-indigo-700">Go to cart</a>.
        </div>
    @elseif ($shippingAddresses->isEmpty() || $billingAddresses->isEmpty())
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-sm text-amber-800">
            Add at least one shipping and one billing address before placing an order.
            <a href="{{ route('customer.addresses.create') }}" class="ml-2 font-semibold text-amber-900 underline">Add address</a>
        </div>
    @else
        <form method="POST" action="{{ route('customer.checkout.place') }}" class="space-y-6">
            @csrf

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-lg font-semibold">Shipping Address</h2>
                    <select name="shipping_address_id" class="w-full rounded-md border-gray-300 text-sm" required>
                        @foreach ($shippingAddresses as $address)
                            <option value="{{ $address->id }}" @selected(old('shipping_address_id', $shippingAddresses->firstWhere('is_default', true)?->id) == $address->id)>
                                {{ $address->full_name }} - {{ $address->line_1 }}, {{ $address->city }}
                            </option>
                        @endforeach
                    </select>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-lg font-semibold">Billing Address</h2>
                    <select name="billing_address_id" class="w-full rounded-md border-gray-300 text-sm" required>
                        @foreach ($billingAddresses as $address)
                            <option value="{{ $address->id }}" @selected(old('billing_address_id', $billingAddresses->firstWhere('is_default', true)?->id) == $address->id)>
                                {{ $address->full_name }} - {{ $address->line_1 }}, {{ $address->city }}
                            </option>
                        @endforeach
                    </select>
                </section>
            </div>

            <div class="space-y-4">
                @foreach ($summary['groups'] as $group)
                    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="text-base font-semibold">{{ $group['vendor']?->store_name ?? 'Vendor #'.$group['vendor_id'] }}</h3>
                            <p class="text-sm text-gray-600">Vendor Total: <span class="font-semibold">{{ number_format($group['total'], 2) }}</span></p>
                        </div>
                        <div class="space-y-2 text-sm">
                            @foreach ($group['items'] as $item)
                                <div class="flex items-center justify-between">
                                    <span>{{ $item->product?->name }} x {{ $item->quantity }}</span>
                                    <span>{{ number_format((float) $item->line_total, 2) }}</span>
                                </div>
                            @endforeach
                            <div class="mt-2 border-t border-gray-100 pt-2 text-gray-600">
                                <div class="flex items-center justify-between">
                                    <span>Subtotal</span>
                                    <span>{{ number_format($group['subtotal'], 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Shipping</span>
                                    <span>{{ number_format($group['shipping_amount'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div>
                    <label for="provider" class="text-sm font-medium text-gray-700">Payment Method</label>
                    <select id="provider" name="provider" class="mt-1 w-full rounded-md border-gray-300 text-sm" required>
                        <option value="stripe" @selected(old('provider', 'stripe') === 'stripe')>Stripe</option>
                        <option value="cod" @selected(old('provider') === 'cod')>Cash on Delivery</option>
                    </select>
                </div>
                <div class="flex items-center justify-between text-sm text-gray-700">
                    <span>Subtotal</span>
                    <span>{{ number_format($summary['subtotal'], 2) }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between text-sm text-gray-700">
                    <span>Shipping</span>
                    <span>{{ number_format($summary['shipping_amount'], 2) }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between text-lg font-semibold text-gray-900">
                    <span>Grand Total</span>
                    <span>{{ number_format($summary['grand_total'], 2) }}</span>
                </div>
                <button type="submit" class="mt-4 w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Place Order
                </button>
            </div>
        </form>
    @endif
@endsection

@push('scripts')
<script>
    (function ($) {
        if (!$) return;
        const keyPrefix = 'checkout-address-';

        $('select[name="shipping_address_id"], select[name="billing_address_id"]').each(function () {
            const name = $(this).attr('name');
            const saved = window.localStorage.getItem(keyPrefix + name);
            if (saved) {
                $(this).val(saved);
            }
        });

        $(document).on('change', 'select[name="shipping_address_id"], select[name="billing_address_id"]', function () {
            const name = $(this).attr('name');
            window.localStorage.setItem(keyPrefix + name, $(this).val());
        });
    })(window.jQuery || window.$);
</script>
@endpush
