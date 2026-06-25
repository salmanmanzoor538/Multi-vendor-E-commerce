@extends('layouts.master', ['title' => 'My Addresses'])

@section('content')
    @include('partials._alerts')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">My Addresses</h1>
            <p class="mt-1 text-sm text-gray-600">Manage shipping and billing addresses for checkout.</p>
        </div>
        <a href="{{ route('customer.addresses.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Add Address</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Shipping Addresses</h2>
            @forelse($shippingAddresses as $address)
                <div class="mb-4 rounded-md border border-gray-100 p-4">
                    <p class="font-semibold text-gray-900">{{ $address->full_name }} @if($address->is_default)<span class="ml-2 rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">Default</span>@endif</p>
                    <p class="text-sm text-gray-600">{{ $address->line_1 }}{{ $address->line_2 ? ', '.$address->line_2 : '' }}</p>
                    <p class="text-sm text-gray-600">{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
                    <p class="text-sm text-gray-600">{{ strtoupper($address->country_code) }} · {{ $address->phone }}</p>
                    <div class="mt-3 flex items-center gap-3">
                        <a href="{{ route('customer.addresses.edit', $address) }}" class="text-sm text-indigo-600 hover:text-indigo-700">Edit</a>
                        <form method="POST" action="{{ route('customer.addresses.destroy', $address) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-700">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-600">No shipping addresses added yet.</p>
            @endforelse
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Billing Addresses</h2>
            @forelse($billingAddresses as $address)
                <div class="mb-4 rounded-md border border-gray-100 p-4">
                    <p class="font-semibold text-gray-900">{{ $address->full_name }} @if($address->is_default)<span class="ml-2 rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">Default</span>@endif</p>
                    <p class="text-sm text-gray-600">{{ $address->line_1 }}{{ $address->line_2 ? ', '.$address->line_2 : '' }}</p>
                    <p class="text-sm text-gray-600">{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
                    <p class="text-sm text-gray-600">{{ strtoupper($address->country_code) }} · {{ $address->phone }}</p>
                    <div class="mt-3 flex items-center gap-3">
                        <a href="{{ route('customer.addresses.edit', $address) }}" class="text-sm text-indigo-600 hover:text-indigo-700">Edit</a>
                        <form method="POST" action="{{ route('customer.addresses.destroy', $address) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-700">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-600">No billing addresses added yet.</p>
            @endforelse
        </section>
    </div>
@endsection
