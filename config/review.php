<?php

use App\Enums\ReviewStatus;

return [
    'rating' => [
        'min' => (int) env('REVIEW_RATING_MIN', 1),
        'max' => (int) env('REVIEW_RATING_MAX', 5),
    ],
    'pagination' => [
        'admin_per_page' => (int) env('REVIEW_ADMIN_PER_PAGE', 15),
        'vendor_per_page' => (int) env('REVIEW_VENDOR_PER_PAGE', 15),
    ],
    'statuses' => array_column(ReviewStatus::cases(), 'value'),
];
