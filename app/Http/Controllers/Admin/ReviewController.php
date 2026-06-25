<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Review\ModerateReviewRequest;
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

        return view('admin.reviews.index', [
            'reviews' => $this->reviewService->paginateForAdmin($status),
            'activeStatus' => $status,
            'statusOptions' => ReviewStatus::cases(),
        ]);
    }

    public function moderate(ModerateReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('moderate', $review);

        $validated = $request->validated();

        $this->reviewService->moderate(
            $review,
            ReviewStatus::from((string) $validated['status']),
            $request->user(),
            $validated['moderation_note'] ?? null
        );

        return redirect()
            ->route('admin.reviews.index', ['status' => $request->input('status_filter') ?: null])
            ->with('status', 'Review moderation updated successfully.');
    }

    private function resolveStatusFilter(?string $status): ?string
    {
        if (! $status) {
            return null;
        }

        return in_array($status, config('review.statuses', []), true) ? $status : null;
    }
}
