<section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <h2 class="mb-4 text-base font-semibold">Order Timeline</h2>

    <div class="space-y-4">
        @forelse ($order->statusHistories as $history)
            <div class="rounded-md border border-gray-100 p-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="text-sm">
                        @if ($history->from_status)
                            <span class="text-gray-500">{{ $history->from_status->label() }}</span>
                            <span class="mx-2 text-gray-400">-></span>
                        @endif

                        @include('orders.partials._status_badge', ['status' => $history->to_status])
                    </div>
                    <span class="text-xs text-gray-500">{{ $history->created_at?->format('Y-m-d H:i') }}</span>
                </div>

                <p class="mt-2 text-xs text-gray-500">
                    By: {{ $history->changedBy?->name ?? 'System' }}
                </p>

                @if (! empty($history->note))
                    <p class="mt-2 text-sm text-gray-700">{{ $history->note }}</p>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-600">No timeline entries found.</p>
        @endforelse
    </div>
</section>
