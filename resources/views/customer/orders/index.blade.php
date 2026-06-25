@extends('layouts.master', ['title' => 'My Orders'])

@section('content')
    @include('partials._alerts')

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">My Orders</h1>
        <p class="mt-1 text-sm text-gray-600">Each checkout may create multiple vendor orders.</p>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Order #</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Vendor</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Payment</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Total</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-4 py-3">{{ $order->order_number }}</td>
                        <td class="px-4 py-3">{{ $order->vendor?->store_name ?? 'Vendor #'.$order->vendor_id }}</td>
                        <td class="px-4 py-3">
                            @include('orders.partials._status_badge', ['status' => $order->status])
                        </td>
                        <td class="px-4 py-3">{{ ucfirst($order->payment_status->value) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $order->grand_total, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('customer.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-700">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-600">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
@endsection
