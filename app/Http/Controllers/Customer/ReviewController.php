<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviewService)
    {
    }

    public function store(StoreReviewRequest $request): RedirectResponse
    {
        $this->authorize('create', Review::class);

        $review = $this->reviewService->createForCustomer($request->user(), $request->validated());
        $orderId = $review->orderItem?->order_id;

        if ($orderId) {
            return redirect()
                ->route('customer.orders.show', $orderId)
                ->with('status', 'Review submitted and pending admin moderation.');
        }

        return redirect()
            ->route('customer.orders.index')
            ->with('status', 'Review submitted and pending admin moderation.');
    }
}
