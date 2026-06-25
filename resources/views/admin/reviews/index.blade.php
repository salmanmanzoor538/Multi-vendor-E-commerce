@extends('layouts.master', ['title' => 'Review Moderation'])

@section('content')
    @include('partials._alerts')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Review Moderation</h1>
            <p class="mt-1 text-sm text-gray-600">Approve or reject customer reviews before publication.</p>
        </div>
    </div>

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="flex items-end gap-3">
            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="rounded-md border-gray-300 text-sm">
                    <option value="">All</option>
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected($activeStatus === $statusOption->value)>
                            {{ $statusOption->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                Filter
            </button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse ($reviews as $review)
            <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">{{ $review->product?->name ?? 'Product #'.$review->product_id }}</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Customer: {{ $review->user?->name ?? 'User #'.$review->user_id }}
                            <span class="mx-2 text-gray-400">|</span>
                            Vendor: {{ $review->vendor?->store_name ?? 'Vendor #'.$review->vendor_id }}
                        </p>
                        <p class="mt-1 text-sm text-gray-700">Rating: <span class="font-semibold">{{ $review->rating }}/5</span></p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                        {{ $review->status->label() }}
                    </span>
                </div>

                <p class="mt-3 text-sm text-gray-700">{{ $review->comment }}</p>

                @if ($review->vendor_reply)
                    <div class="mt-3 rounded-md border border-indigo-200 bg-indigo-50 p-3">
                        <p class="text-xs font-semibold text-indigo-700">Vendor Reply</p>
                        <p class="mt-1 text-sm text-indigo-900">{{ $review->vendor_reply }}</p>
                    </div>
                @endif

                @if ($review->moderation_note)
                    <p class="mt-3 text-xs text-gray-500">Previous Moderation Note: {{ $review->moderation_note }}</p>
                @endif

                <form action="{{ route('admin.reviews.moderate', $review) }}" method="POST" class="mt-4 grid gap-3 rounded-md border border-gray-100 p-3 md:grid-cols-3">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_filter" value="{{ $activeStatus }}">

                    <div>
                        <label for="status_{{ $review->id }}" class="mb-1 block text-xs font-medium text-gray-700">Action</label>
                        <select id="status_{{ $review->id }}" name="status" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="moderation_note_{{ $review->id }}" class="mb-1 block text-xs font-medium text-gray-700">Moderation Note (optional)</label>
                        <input
                            id="moderation_note_{{ $review->id }}"
                            name="moderation_note"
                            type="text"
                            class="w-full rounded-md border-gray-300 text-sm"
                            placeholder="Optional note"
                        >
                    </div>

                    <div class="md:col-span-3">
                        <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                            Save Moderation
                        </button>
                    </div>
                </form>
            </article>
        @empty
            <div class="rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm">
                No reviews found for the selected filter.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $reviews->links() }}
    </div>
@endsection
