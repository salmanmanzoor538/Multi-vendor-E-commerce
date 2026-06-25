@extends('layouts.master', ['title' => 'Create Plan'])

@section('content')
    @include('partials._alerts')

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold">Create Plan</h1>
        <p class="mt-1 text-sm text-gray-600">Add a new vendor subscription plan.</p>

        <form action="{{ route('admin.plans.store') }}" method="POST" class="mt-6">
            @csrf
            @include('admin.plans.partials._form')
        </form>
    </div>
@endsection
