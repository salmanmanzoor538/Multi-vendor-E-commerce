@extends('layouts.master', ['title' => 'Create Category'])

@section('content')
    @include('partials._alerts')

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold">Create Category</h1>
        <p class="mt-1 text-sm text-gray-600">Add a new category in the global tree.</p>

        <form action="{{ route('admin.categories.store') }}" method="POST" class="mt-6">
            @csrf
            @include('vendor.categories.partials._form', ['routePrefix' => 'admin'])
        </form>
    </div>
@endsection
