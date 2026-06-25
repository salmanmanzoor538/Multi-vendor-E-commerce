@extends('layouts.master', ['title' => 'Payment Submitted'])

@section('content')
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-6">
        <h1 class="text-2xl font-semibold text-emerald-900">Payment Submitted</h1>
        @if ($provider === 'cod')
            <p class="mt-2 text-sm text-emerald-800">
                Your order has been placed with Cash on Delivery.
            </p>
        @else
            <p class="mt-2 text-sm text-emerald-800">
                Your checkout has been initiated. Orders are marked paid only after Stripe webhook confirmation.
            </p>
        @endif
        @if ($checkoutBatchId)
            <p class="mt-2 text-sm text-emerald-800">Checkout Batch: <span class="font-medium">{{ $checkoutBatchId }}</span></p>
        @endif
        @if ($sessionId)
            <p class="mt-1 text-xs text-emerald-700">Stripe Session: {{ $sessionId }}</p>
        @endif
        <a href="{{ route('customer.orders.index') }}" class="mt-4 inline-flex rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800">
            View Orders
        </a>
    </div>
@endsection
