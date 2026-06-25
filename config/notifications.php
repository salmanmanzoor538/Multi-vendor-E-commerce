<?php

use App\Enums\OrderStatus;

$allOrderStatuses = array_map(
    static fn (OrderStatus $status): string => $status->value,
    OrderStatus::cases()
);

return [
    'order_updates' => [
        'poll_interval_seconds' => (int) env('ORDER_NOTIFICATIONS_POLL_INTERVAL_SECONDS', 30),
        'feed_limit' => (int) env('ORDER_NOTIFICATIONS_FEED_LIMIT', 10),
        'mail_enabled' => (bool) env('ORDER_NOTIFICATIONS_MAIL_ENABLED', true),
        'mail_statuses' => array_values(array_filter(
            array_map('trim', explode(',', (string) env('ORDER_NOTIFICATIONS_MAIL_STATUSES', implode(',', $allOrderStatuses)))),
            static fn (string $value): bool => $value !== ''
        )),
    ],
];

