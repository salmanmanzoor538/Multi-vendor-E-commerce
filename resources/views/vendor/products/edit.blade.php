@extends('layouts.master', ['title' => 'Edit Product'])

@section('content')
    @include('partials._alerts')

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold">Edit Product</h1>
        <p class="mt-1 text-sm text-gray-600">Update product details, categories, and images.</p>

        <form action="{{ route('vendor.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="mt-6">
            @csrf
            @method('PUT')
            @include('vendor.products.partials._form', ['product' => $product])
        </form>
    </div>
@endsection
