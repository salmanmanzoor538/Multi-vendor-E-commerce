@extends('layouts.master', ['title' => 'Order Details'])

@section('content')
    @include('partials._alerts')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Order {{ $order->order_number }}</h1>
            <p class="mt-1 text-sm text-gray-600">Vendor: {{ $order->vendor?->store_name ?? 'Vendor #'.$order->vendor_id }}</p>
        </div>
        <a href="{{ route('customer.orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Orders</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
            <h2 class="mb-3 text-base font-semibold">Items</h2>
            <div class="space-y-3">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between rounded-md border border-gray-100 p-4">
                        <div class="w-full">
                            <p class="text-sm font-semibold text-gray-900">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-500">SKU: {{ $item->sku_snapshot }}</p>

                            @php
                                $existingReview = $reviewsByOrderItem->get($item->id);
                                $isDelivered = $order->status === \App\Enums\OrderStatus::DELIVERED;
                                $canCreateReview = $isDelivered && $item->product_id && $item->vendor_id;
                            @endphp

                            @if ($existingReview)
                                <div class="mt-3 rounded-md border border-gray-200 bg-gray-50 p-3">
                                    <p class="text-xs text-gray-600">
                                        Review Status:
                                        <span class="font-semibold">{{ $existingReview->status->label() }}</span>
                                    </p>
                                    <p class="mt-1 text-sm text-gray-700">
                                        Rating: <span class="font-semibold">{{ $existingReview->rating }}/5</span>
                                    </p>
                                    <p class="mt-1 text-sm text-gray-700">{{ $existingReview->comment }}</p>
                                    @if ($existingReview->vendor_reply)
                                        <div class="mt-2 rounded-md border border-indigo-200 bg-indigo-50 p-2">
                                            <p class="text-xs font-semibold text-indigo-700">Vendor Reply</p>
                                            <p class="mt-1 text-sm text-indigo-900">{{ $existingReview->vendor_reply }}</p>
                                        </div>
                                    @endif
                                    @if ($existingReview->moderation_note)
                                        <p class="mt-2 text-xs text-gray-500">Moderation Note: {{ $existingReview->moderation_note }}</p>
                                    @endif
                                </div>
                            @elseif ($canCreateReview)
                                <form action="{{ route('customer.reviews.store') }}" method="POST" class="mt-3 space-y-2 rounded-md border border-gray-200 bg-gray-50 p-3">
                                    @csrf
                                    <input type="hidden" name="order_item_id" value="{{ $item->id }}">

                                    <div>
                                        <label for="rating_{{ $item->id }}" class="block text-xs font-medium text-gray-700">Rating</label>
                                        <select id="rating_{{ $item->id }}" name="rating" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                                            @for ($star = config('review.rating.max', 5); $star >= config('review.rating.min', 1); $star--)
                                                <option value="{{ $star }}">{{ $star }} / 5</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div>
                                        <label for="comment_{{ $item->id }}" class="block text-xs font-medium text-gray-700">Comment</label>
                                        <textarea id="comment_{{ $item->id }}" name="comment" rows="3" class="mt-1 w-full rounded-md border-gray-300 text-sm" required></textarea>
                                    </div>

                                    <button type="submit" class="inline-flex rounded-md bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                                        Submit Review
                                    </button>
                                </form>
                            @elseif ($isDelivered)
                                <p class="mt-3 text-xs text-gray-500">This item is not eligible for review.</p>
                            @endif
                        </div>
                        <div class="text-right text-sm">
                            <p>Qty: {{ $item->quantity }}</p>
                            <p class="font-semibold">{{ number_format((float) $item->line_total, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-3 text-base font-semibold">Summary</h2>
            <div class="space-y-2 text-sm text-gray-700">
                <div class="flex items-center justify-between">
                    <span>Status</span>
                    @include('orders.partials._status_badge', ['status' => $order->status])
                </div>
                <div class="flex items-center justify-between">
                    <span>Payment</span>
                    <span class="font-medium">{{ ucfirst($order->payment_status->value) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Subtotal</span>
                    <span>{{ number_format((float) $order->subtotal, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Shipping</span>
                    <span>{{ number_format((float) $order->shipping_amount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between border-t border-gray-100 pt-2 text-base font-semibold text-gray-900">
                    <span>Total</span>
                    <span>{{ number_format((float) $order->grand_total, 2) }}</span>
                </div>
            </div>
        </section>
    </div>

    <div class="mt-6">
        @include('orders.partials._timeline', ['order' => $order])
    </div>
@endsection
