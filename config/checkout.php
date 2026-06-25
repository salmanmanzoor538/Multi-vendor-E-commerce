<?php

return [
    'currency' => env('CHECKOUT_CURRENCY', 'usd'),
    'order' => [
        'number_prefix' => env('CHECKOUT_ORDER_PREFIX', 'ORD'),
    ],
    'shipping' => [
        'flat_per_vendor' => (float) env('CHECKOUT_FLAT_SHIPPING_PER_VENDOR', 5),
    ],
    'statuses' => [
        'order' => ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'],
        'payment' => ['pending', 'paid', 'failed', 'refunded'],
    ],
    'stripe' => [
        'success_url' => env('CHECKOUT_STRIPE_SUCCESS_URL'),
        'cancel_url' => env('CHECKOUT_STRIPE_CANCEL_URL'),
        'webhook_tolerance_seconds' => (int) env('STRIPE_WEBHOOK_TOLERANCE_SECONDS', 300),
    ],
];
