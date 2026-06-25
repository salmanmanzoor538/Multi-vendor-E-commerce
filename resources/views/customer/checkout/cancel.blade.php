@extends('layouts.master', ['title' => 'Checkout Cancelled'])

@section('content')
    <div class="rounded-lg border border-amber-200 bg-amber-50 p-6">
        <h1 class="text-2xl font-semibold text-amber-900">Checkout Cancelled</h1>
        <p class="mt-2 text-sm text-amber-800">
            Stripe checkout was cancelled. You can retry payment from the checkout page.
        </p>
        @if ($checkoutBatchId)
            <p class="mt-2 text-sm text-amber-800">Checkout Batch: <span class="font-medium">{{ $checkoutBatchId }}</span></p>
        @endif
        <a href="{{ route('customer.checkout.summary') }}" class="mt-4 inline-flex rounded-md bg-amber-700 px-4 py-2 text-sm font-medium text-white hover:bg-amber-800">
            Return to Checkout
        </a>
    </div>
@endsection
