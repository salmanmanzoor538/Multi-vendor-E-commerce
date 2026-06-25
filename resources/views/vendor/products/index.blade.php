@extends('layouts.master', ['title' => 'Products'])

@section('content')
    @include('partials._alerts')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Products</h1>
            <p class="mt-1 text-sm text-gray-600">Manage your vendor catalog.</p>
        </div>
        <a href="{{ route('vendor.products.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Add Product</a>
    </div>

    @include('vendor.products.partials._table', ['products' => $products])

    <div class="mt-6">
        {{ $products->links() }}
    </div>
@endsection
