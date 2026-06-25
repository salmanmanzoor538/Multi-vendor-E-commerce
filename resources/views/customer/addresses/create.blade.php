@extends('layouts.master', ['title' => 'Add Address'])

@section('content')
    @include('partials._alerts')

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-semibold">Add Address</h1>
        <p class="mt-1 text-sm text-gray-600">Save a shipping or billing address for checkout.</p>

        <form method="POST" action="{{ route('customer.addresses.store') }}" class="mt-6 space-y-6">
            @csrf
            @include('customer.addresses._form')

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Save Address</button>
                <a href="{{ route('customer.addresses.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
@endsection
