@extends('layouts.master', ['title' => 'Product Reviews'])

@section('content')
    @include('partials._alerts')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Product Reviews</h1>
            <p class="mt-1 text-sm text-gray-600">Manage customer feedback and post public replies.</p>
        </div>
    </div>

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('vendor.reviews.index') }}" class="flex items-end gap-3">
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
                        <p class="mt-1 text-sm text-gray-600">Customer: {{ $review->user?->name ?? 'User #'.$review->user_id }}</p>
                        <p class="mt-1 text-sm text-gray-700">Rating: <span class="font-semibold">{{ $review->rating }}/5</span></p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                        {{ $review->status->label() }}
                    </span>
                </div>

                <p class="mt-3 text-sm text-gray-700">{{ $review->comment }}</p>

                @if ($review->status->value === \App\Enums\ReviewStatus::APPROVED->value)
                    <form action="{{ route('vendor.reviews.reply', $review) }}" method="POST" class="mt-4 rounded-md border border-gray-100 p-3">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status_filter" value="{{ $activeStatus }}">

                        <label for="vendor_reply_{{ $review->id }}" class="mb-1 block text-xs font-medium text-gray-700">Vendor Reply</label>
                        <textarea
                            id="vendor_reply_{{ $review->id }}"
                            name="vendor_reply"
                            rows="3"
                            class="w-full rounded-md border-gray-300 text-sm"
                            required
                        >{{ old('vendor_reply', $review->vendor_reply) }}</textarea>

                        <button type="submit" class="mt-3 rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                            Save Reply
                        </button>
                    </form>
                @elseif ($review->vendor_reply)
                    <div class="mt-3 rounded-md border border-indigo-200 bg-indigo-50 p-3">
                        <p class="text-xs font-semibold text-indigo-700">Vendor Reply</p>
                        <p class="mt-1 text-sm text-indigo-900">{{ $review->vendor_reply }}</p>
                    </div>
                @endif
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
