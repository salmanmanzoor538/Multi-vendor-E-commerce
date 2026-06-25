@extends('layouts.master', ['title' => 'Edit Store'])

@section('content')
    <div class="mx-auto max-w-3xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-semibold">Edit Store Profile</h1>

        @if (session('status'))
            <div class="mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('vendor.store.update') }}" class="mt-6 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="store_name" class="block text-sm font-medium text-gray-700">Store Name</label>
                <input id="store_name" type="text" name="store_name" value="{{ old('store_name', $vendor->store_name) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                @error('store_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="store_slug" class="block text-sm font-medium text-gray-700">Store Slug</label>
                <input id="store_slug" type="text" name="store_slug" value="{{ old('store_slug', $vendor->store_slug) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                @error('store_slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="business_email" class="block text-sm font-medium text-gray-700">Business Email</label>
                <input id="business_email" type="email" name="business_email" value="{{ old('business_email', $vendor->business_email) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                @error('business_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="business_phone" class="block text-sm font-medium text-gray-700">Business Phone</label>
                <input id="business_phone" type="text" name="business_phone" value="{{ old('business_phone', $vendor->business_phone) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                @error('business_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="4" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $vendor->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <textarea id="address" name="address" rows="3" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('address', $vendor->address) }}</textarea>
                @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <button type="submit" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection
