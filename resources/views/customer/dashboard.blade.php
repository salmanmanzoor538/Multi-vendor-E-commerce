@extends('layouts.master', ['title' => 'Customer Dashboard'])

@section('content')
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-semibold">Customer Dashboard</h1>
        <p class="mt-2 text-sm text-gray-600">Review your profile, addresses, cart, and orders.</p>
        <div class="mt-4 flex flex-wrap gap-3 text-sm">
            <a href="{{ route('customer.addresses.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-gray-700 hover:bg-gray-50">Manage Addresses</a>
            <a href="{{ route('customer.cart.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-gray-700 hover:bg-gray-50">Open Cart</a>
            <a href="{{ route('customer.checkout.summary') }}" class="rounded-md border border-gray-300 px-3 py-2 text-gray-700 hover:bg-gray-50">Checkout</a>
            <a href="{{ route('customer.orders.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-gray-700 hover:bg-gray-50">My Orders</a>
        </div>
    </div>
@endsection
