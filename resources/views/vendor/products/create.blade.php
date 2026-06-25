@extends('layouts.master', ['title' => 'Create Product'])

@section('content')
    @include('partials._alerts')

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold">Create Product</h1>
        <p class="mt-1 text-sm text-gray-600">Add a new product to your store catalog.</p>

        <form action="{{ route('vendor.products.store') }}" method="POST" enctype="multipart/form-data" class="mt-6">
            @csrf
            @include('vendor.products.partials._form')
        </form>
    </div>
@endsection
