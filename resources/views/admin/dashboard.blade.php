@extends('layouts.master', ['title' => 'Admin Dashboard'])

@section('content')
    <div
        id="admin-analytics-dashboard"
        data-endpoint="{{ route('admin.analytics.index') }}"
        data-default-range="{{ config('analytics.dashboard.default_range', '12m') }}"
        class="space-y-6"
    >
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Admin Analytics Dashboard</h1>
                    <p class="mt-2 text-sm text-gray-600">Track paid revenue, vendor performance, and order activity.</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="inline-flex rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                    Manage Orders
                </a>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
                <button type="button" data-range="30d" class="analytics-range-btn rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">30 Days</button>
                <button type="button" data-range="90d" class="analytics-range-btn rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">90 Days</button>
                <button type="button" data-range="12m" class="analytics-range-btn rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">12 Months</button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">MRR (Current Month)</p>
                <p id="kpi-mrr" class="mt-2 text-2xl font-semibold text-gray-900">-</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Total Sales (Paid)</p>
                <p id="kpi-total-sales" class="mt-2 text-2xl font-semibold text-gray-900">-</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Orders (Placed)</p>
                <p id="kpi-orders-count" class="mt-2 text-2xl font-semibold text-gray-900">-</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">MRR by Month</h2>
                <canvas id="chart-mrr" class="mt-4 h-64"></canvas>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Sales Over Time</h2>
                <canvas id="chart-sales" class="mt-4 h-64"></canvas>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Orders Over Time</h2>
                <canvas id="chart-orders" class="mt-4 h-64"></canvas>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Order Status Distribution</h2>
                <canvas id="chart-status-distribution" class="mt-4 h-64"></canvas>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Vendor Performance (Paid Sales)</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Vendor</th>
                            <th class="px-3 py-2 text-right font-semibold text-gray-600">Total Sales</th>
                            <th class="px-3 py-2 text-right font-semibold text-gray-600">Orders</th>
                        </tr>
                    </thead>
                    <tbody id="vendor-performance-table" class="divide-y divide-gray-100 bg-white">
                        <tr>
                            <td colspan="3" class="px-3 py-3 text-center text-gray-500">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Recent Orders</h2>
            <div class="mt-3 space-y-3">
                @forelse ($recentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between rounded-md border border-gray-100 p-3 hover:bg-gray-50">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500">{{ $order->vendor?->store_name ?? 'Vendor #'.$order->vendor_id }}</p>
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

@push('scripts')
    @vite('resources/js/admin-analytics-dashboard.js')
@endpush
