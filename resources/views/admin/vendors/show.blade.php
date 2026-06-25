@extends('layouts.master', ['title' => 'Vendor Review'])

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        @if (session('status'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">{{ $vendor->store_name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">Slug: {{ $vendor->store_slug }}</p>
                    <p class="mt-1 text-sm text-gray-600">Status: {{ $statusLabels[$vendor->status->value] ?? ucfirst($vendor->status->value) }}</p>
                </div>
                <a href="{{ route('admin.vendors.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Back to list</a>
            </div>

            <dl class="mt-6 grid grid-cols-1 gap-4 text-sm text-gray-700 sm:grid-cols-2">
                <div>
                    <dt class="font-semibold text-gray-900">Owner</dt>
                    <dd>{{ $vendor->owner->name }}</dd>
                    <dd>{{ $vendor->owner->email }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-900">Business Email</dt>
                    <dd>{{ $vendor->business_email ?: 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-900">Business Phone</dt>
                    <dd>{{ $vendor->business_phone ?: 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-900">Approved At</dt>
                    <dd>{{ $vendor->approved_at?->format('Y-m-d H:i') ?: 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="font-semibold text-gray-900">Description</dt>
                    <dd>{{ $vendor->description ?: 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="font-semibold text-gray-900">Address</dt>
                    <dd>{{ $vendor->address ?: 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="font-semibold text-gray-900">Rejection Reason</dt>
                    <dd>{{ $vendor->rejection_reason ?: 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="rounded-lg border border-green-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Approve Vendor</h2>
                <p class="mt-2 text-sm text-gray-600">Approve this vendor and grant access to protected vendor routes.</p>

                <form method="POST" action="{{ route('admin.vendors.approve', $vendor) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Approve</button>
                </form>
            </div>

            <div class="rounded-lg border border-red-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Reject Vendor</h2>
                <p class="mt-2 text-sm text-gray-600">Reject this vendor application with a reason.</p>

                <form method="POST" action="{{ route('admin.vendors.reject', $vendor) }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Reject</button>
                </form>
            </div>
        </div>
    </div>
@endsection
