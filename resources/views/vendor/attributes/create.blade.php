@extends('layouts.master', ['title' => 'Create Attribute'])

@section('content')
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-semibold">Create Attribute</h1>
        <p class="mt-1 text-sm text-gray-600">Define an attribute and its possible values.</p>

        @if ($errors->any())
            <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('vendor.attributes.store') }}" method="POST" class="mt-6">
            @csrf
            @include('vendor.attributes._form')
        </form>
    </div>
@endsection
