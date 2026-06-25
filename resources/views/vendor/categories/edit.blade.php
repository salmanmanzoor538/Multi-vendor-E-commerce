@extends('layouts.master', ['title' => 'Edit Category'])

@section('content')
    @include('partials._alerts')

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold">Edit Category</h1>
        <p class="mt-1 text-sm text-gray-600">Update category information and hierarchy.</p>

        <form action="{{ route('vendor.categories.update', $category) }}" method="POST" class="mt-6">
            @csrf
            @method('PUT')
            @include('vendor.categories.partials._form', ['routePrefix' => 'vendor', 'category' => $category])
        </form>
    </div>
@endsection
