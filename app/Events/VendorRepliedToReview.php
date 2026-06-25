<?php

namespace App\Events;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VendorRepliedToReview
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Review $review,
        public readonly User $vendorUser,
    ) {
    }
}
