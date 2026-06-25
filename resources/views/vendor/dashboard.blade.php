@extends('layouts.master', ['title' => 'Vendor Dashboard'])

@section('content')
    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold">Vendor Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600">Track your store performance, products, and incoming orders.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('vendor.store.edit') }}" class="inline-flex rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                    Manage Store Profile
                </a>
                <a href="{{ route('vendor.orders.index') }}" class="inline-flex rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    View Orders
                </a>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Recent Orders</h2>
            <div class="mt-3 space-y-3">
                @forelse ($recentOrders as $order)
                    <a href="{{ route('vendor.orders.show', $order) }}" class="flex items-center justify-between rounded-md border border-gray-100 p-3 hover:bg-gray-50">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500">Customer: {{ $order->user?->name ?? 'Customer #'.$order->user_id }}</p>
                        </div>
                        @include('orders.partials._status_badge', ['status' => $order->status])
                    </a>
                @empty
                    <p class="text-sm text-gray-600">No orders found.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
