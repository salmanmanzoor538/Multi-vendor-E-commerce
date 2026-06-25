<?php

return [
    'cache' => [
        'ttl' => [
            'analytics_seconds' => env('PERF_CACHE_ANALYTICS_SECONDS', 300),
            'taxonomy_seconds' => env('PERF_CACHE_TAXONOMY_SECONDS', 1800),
            'plans_seconds' => env('PERF_CACHE_PLANS_SECONDS', 1800),
            'vendor_attributes_seconds' => env('PERF_CACHE_VENDOR_ATTRIBUTES_SECONDS', 1800),
            'vendor_subscription_seconds' => env('PERF_CACHE_VENDOR_SUBSCRIPTION_SECONDS', 300),
        ],
        'namespaces' => [
            'analytics' => 'analytics',
            'categories' => 'categories',
            'plans' => 'plans',
            'vendor_attributes' => 'vendor_attributes',
            'vendor_subscription' => 'vendor_subscription',
        ],
    ],
];

