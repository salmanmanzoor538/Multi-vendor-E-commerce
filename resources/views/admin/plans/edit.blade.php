@extends('layouts.master', ['title' => 'Edit Plan'])

@section('content')
    @include('partials._alerts')

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold">Edit Plan</h1>
        <p class="mt-1 text-sm text-gray-600">Update plan pricing and limits.</p>

        <form action="{{ route('admin.plans.update', $plan) }}" method="POST" class="mt-6">
            @csrf
            @method('PUT')
            @include('admin.plans.partials._form', ['plan' => $plan])
        </form>
    </div>
@endsection
