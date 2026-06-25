@extends('layouts.master', ['title' => 'Admin Categories'])

@section('content')
    @include('partials._alerts')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Categories</h1>
            <p class="mt-1 text-sm text-gray-600">Manage the global category tree for all vendors.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Add Category</a>
    </div>

    @include('vendor.categories.partials._table', ['routePrefix' => 'admin'])
@endsection
