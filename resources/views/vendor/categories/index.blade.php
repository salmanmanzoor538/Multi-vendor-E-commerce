@extends('layouts.master', ['title' => 'Vendor Categories'])

@section('content')
    @include('partials._alerts')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Categories</h1>
            <p class="mt-1 text-sm text-gray-600">Manage reusable product categories.</p>
        </div>
        @can('create', \App\Models\Category::class)
            <a href="{{ route('vendor.categories.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Add Category</a>
        @endcan
    </div>

    @include('vendor.categories.partials._table', ['routePrefix' => 'vendor'])
@endsection
