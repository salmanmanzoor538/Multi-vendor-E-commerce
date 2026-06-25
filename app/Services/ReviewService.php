<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Events\ReviewModerated;
use App\Events\ReviewSubmitted;
use App\Events\VendorRepliedToReview;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function __construct(private readonly ReviewRepositoryInterface $reviewRepository)
    {
    }

    public function createForCustomer(User $customer, array $data): Review
    {
        $orderItem = OrderItem::query()
            ->with(['order'])
            ->findOrFail((int) $data['order_item_id']);

        $this->assertCanReviewOrderItem($customer, $orderItem);

        if ($this->reviewRepository->findByOrderItemId($orderItem->id)) {
            throw ValidationException::withMessages([
                'order_item_id' => 'A review already exists for this order item.',
            ]);
        }

        $review = DB::transaction(function () use ($customer, $data, $orderItem): Review {
            return $this->reviewRepository->create([
                'vendor_id' => $orderItem->vendor_id,
                'product_id' => $orderItem->product_id,
                'order_item_id' => $orderItem->id,
                'user_id' => $customer->id,
                'rating' => (int) $data['rating'],
                'comment' => (string) $data['comment'],
                'status' => ReviewStatus::PENDING->value,
            ]);
        });

        event(new ReviewSubmitted($review));

        return $review;
    }

    public function moderate(Review $review, ReviewStatus $toStatus, User $admin, ?string $note = null): Review
    {
        $fromStatus = $review->status;

        $updatedReview = DB::transaction(function () use ($review, $toStatus, $admin, $note): Review {
            return $this->reviewRepository->update($review, [
                'status' => $toStatus->value,
                'moderated_by' => $admin->id,
                'moderated_at' => Carbon::now(),
                'moderation_note' => $note,
            ]);
        });

        event(new ReviewModerated(
            $updatedReview,
            $fromStatus,
            $toStatus,
            $admin,
            $note
        ));

        return $updatedReview;
    }

    public function replyAsVendor(Review $review, User $vendorUser, string $reply): Review
    {
        if ($review->status !== ReviewStatus::APPROVED) {
            throw ValidationException::withMessages([
                'vendor_reply' => 'Vendors can only reply to approved reviews.',
            ]);
        }

        $vendorId = (int) ($vendorUser->ownedVendor?->id ?? $vendorUser->vendor_id);
        if ((int) $review->vendor_id !== $vendorId) {
            throw ValidationException::withMessages([
                'vendor_reply' => 'You are not authorized to reply to this review.',
            ]);
        }

        $updatedReview = DB::transaction(function () use ($review, $reply): Review {
            return $this->reviewRepository->update($review, [
                'vendor_reply' => $reply,
                'vendor_replied_at' => Carbon::now(),
            ]);
        });

        event(new VendorRepliedToReview($updatedReview, $vendorUser));

        return $updatedReview;
    }

    public function paginateForVendor(User $vendorUser, ?string $status = null): LengthAwarePaginator
    {
        $vendorId = (int) ($vendorUser->ownedVendor?->id ?? $vendorUser->vendor_id);

        return $this->reviewRepository->paginateForVendor(
            $vendorId,
            (int) config('review.pagination.vendor_per_page', 15),
            $status
        );
    }

    public function paginateForAdmin(?string $status = null): LengthAwarePaginator
    {
        return $this->reviewRepository->paginateForAdmin(
            (int) config('review.pagination.admin_per_page', 15),
            $status
        );
    }

    public function reviewsForUserOrderItems(User $customer, Collection $orderItems): Collection
    {
        $orderItemIds = $orderItems->pluck('id')->map(fn ($id) => (int) $id)->all();

        return $this->reviewRepository
            ->findForUserOrderItems((int) $customer->id, $orderItemIds)
            ->keyBy('order_item_id');
    }

    private function assertCanReviewOrderItem(User $customer, OrderItem $orderItem): void
    {
        $order = $orderItem->order;

        if (! $order) {
            throw ValidationException::withMessages([
                'order_item_id' => 'The selected order item does not belong to a valid order.',
            ]);
        }

        if ((int) $order->user_id !== (int) $customer->id) {
            throw ValidationException::withMessages([
                'order_item_id' => 'You can only review your own purchased items.',
            ]);
        }

        if ($order->status !== OrderStatus::DELIVERED) {
            throw ValidationException::withMessages([
                'order_item_id' => 'You can review only delivered order items.',
            ]);
        }

        if ($orderItem->product_id === null || $orderItem->vendor_id === null) {
            throw ValidationException::withMessages([
                'order_item_id' => 'This order item is no longer eligible for review.',
            ]);
        }
    }
}
