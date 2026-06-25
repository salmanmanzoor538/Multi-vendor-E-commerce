<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Review\VendorReplyReviewRequest;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviewService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Review::class);

        $status = $this->resolveStatusFilter($request->string('status')->toString());

        return view('vendor.reviews.index', [
            'reviews' => $this->reviewService->paginateForVendor($request->user(), $status),
            'activeStatus' => $status,
            'statusOptions' => ReviewStatus::cases(),
        ]);
    }

    public function reply(VendorReplyReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('reply', $review);

        $this->reviewService->replyAsVendor(
            $review,
            $request->user(),
            (string) $request->validated('vendor_reply')
        );

        return redirect()
            ->route('vendor.reviews.index', ['status' => $request->input('status_filter') ?: null])
            ->with('status', 'Review reply saved successfully.');
    }

    private function resolveStatusFilter(?string $status): ?string
    {
        if (! $status) {
            return null;
        }

        return in_array($status, config('review.statuses', []), true) ? $status : null;
    }
}
