<?php

namespace App\Listeners;

use App\Events\ReviewModerated;
use App\Events\ReviewSubmitted;
use App\Events\VendorRepliedToReview;
use Illuminate\Support\Facades\Log;

class LogReviewActivity
{
    public function handle(ReviewSubmitted|ReviewModerated|VendorRepliedToReview $event): void
    {
        if ($event instanceof ReviewSubmitted) {
            Log::info('Review submitted.', [
                'review_id' => $event->review->id,
                'order_item_id' => $event->review->order_item_id,
                'user_id' => $event->review->user_id,
                'product_id' => $event->review->product_id,
            ]);

            return;
        }

        if ($event instanceof ReviewModerated) {
            Log::info('Review moderated.', [
                'review_id' => $event->review->id,
                'from_status' => $event->fromStatus->value,
                'to_status' => $event->toStatus->value,
                'moderated_by' => $event->moderatedBy->id,
                'note' => $event->note,
            ]);

            return;
        }

        Log::info('Vendor replied to review.', [
            'review_id' => $event->review->id,
            'vendor_user_id' => $event->vendorUser->id,
            'vendor_id' => $event->review->vendor_id,
        ]);
    }
}
