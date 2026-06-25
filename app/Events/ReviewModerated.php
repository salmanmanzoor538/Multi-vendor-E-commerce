<?php

namespace App\Events;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewModerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Review $review,
        public readonly ReviewStatus $fromStatus,
        public readonly ReviewStatus $toStatus,
        public readonly User $moderatedBy,
        public readonly ?string $note = null,
    ) {
    }
}
