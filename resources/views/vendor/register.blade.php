@extends('layouts.master', ['title' => 'Vendor Registration'])

@section('content')
    <div class="mx-auto max-w-3xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-semibold">Become a Vendor</h1>

        @if (session('status'))
            <div class="mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($vendor)
            <div class="mt-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                Your application is currently <strong>{{ $statusLabels[$vendor->status->value] ?? ucfirst($vendor->status->value) }}</strong>.
            </div>

            @if ($vendor->rejection_reason)
                <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    <strong>Rejection reason:</strong> {{ $vendor->rejection_reason }}
                </div>
            @endif
        @else
            <form method="POST" action="{{ route('vendor.register.store') }}" class="mt-6 space-y-4">
                @csrf

                @guest
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password" type="password" name="password" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                @endguest

                <div>
                    <label for="store_name" class="block text-sm font-medium text-gray-700">Store Name</label>
                    <input id="store_name" type="text" name="store_name" value="{{ old('store_name') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    @error('store_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="store_slug" class="block text-sm font-medium text-gray-700">Store Slug (optional)</label>
                    <input id="store_slug" type="text" name="store_slug" value="{{ old('store_slug') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    @error('store_slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="business_email" class="block text-sm font-medium text-gray-700">Business Email</label>
                    <input id="business_email" type="email" name="business_email" value="{{ old('business_email') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    @error('business_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="business_phone" class="block text-sm font-medium text-gray-700">Business Phone</label>
                    <input id="business_phone" type="text" name="business_phone" value="{{ old('business_phone') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    @error('business_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Store Description</label>
                    <textarea id="description" name="description" rows="4" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea id="address" name="address" rows="3" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('address') }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <button type="submit" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                        Submit Vendor Application
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection
