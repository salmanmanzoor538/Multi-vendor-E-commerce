@extends('layouts.master', ['title' => 'Order Details'])

@section('content')
    @include('partials._alerts')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Order {{ $order->order_number }}</h1>
            <p class="mt-1 text-sm text-gray-600">
                Customer: {{ $order->user?->name ?? 'Customer #'.$order->user_id }}
                <span class="mx-2 text-gray-400">|</span>
                Vendor: {{ $order->vendor?->store_name ?? 'Vendor #'.$order->vendor_id }}
            </p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Orders</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
            <h2 class="mb-3 text-base font-semibold">Items</h2>
            <div class="space-y-3">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between rounded-md border border-gray-100 p-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-500">SKU: {{ $item->sku_snapshot }}</p>
                        </div>
                        <div class="text-right text-sm">
                            <p>Qty: {{ $item->quantity }}</p>
                            <p class="font-semibold">{{ number_format((float) $item->line_total, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="mb-3 text-base font-semibold">Summary</h2>
                <div class="space-y-2 text-sm text-gray-700">
                    <div class="flex items-center justify-between">
                        <span>Status</span>
                        @include('orders.partials._status_badge', ['status' => $order->status])
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Payment</span>
                        <span class="font-medium">{{ ucfirst($order->payment_status->value) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Subtotal</span>
                        <span>{{ number_format((float) $order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Shipping</span>
                        <span>{{ number_format((float) $order->shipping_amount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-2 text-base font-semibold text-gray-900">
                        <span>Total</span>
                        <span>{{ number_format((float) $order->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="mb-3 text-base font-semibold">Update Status</h2>
                <form action="{{ route('admin.orders.status.update', $order) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="status" class="mb-1 block text-sm font-medium text-gray-700">New Status</label>
                        <select id="status" name="status" class="w-full rounded-md border-gray-300 text-sm">
                            @foreach ($statusOptions as $statusOption)
                                <option value="{{ $statusOption->value }}" @selected(old('status', $order->status->value) === $statusOption->value)>
                                    {{ $statusOption->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="note" class="mb-1 block text-sm font-medium text-gray-700">Note (optional)</label>
                        <textarea id="note" name="note" rows="3" class="w-full rounded-md border-gray-300 text-sm">{{ old('note') }}</textarea>
                        @error('note')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="inline-flex rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                        Update Status
                    </button>
                </form>
            </div>
        </section>
    </div>

    <div class="mt-6">
        @include('orders.partials._timeline', ['order' => $order])
    </div>
@endsection
