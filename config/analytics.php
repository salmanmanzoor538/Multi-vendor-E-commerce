<?php

return [
    'dashboard' => [
        'default_range' => '12m',
        'default_currency' => 'USD',
        'default_vendor_leaderboard_limit' => 10,
        'max_vendor_leaderboard_limit' => 50,
        'ranges' => [
            '30d' => [
                'days' => 30,
                'granularity' => 'day',
            ],
            '90d' => [
                'days' => 90,
                'granularity' => 'day',
            ],
            '12m' => [
                'months' => 12,
                'granularity' => 'month',
            ],
        ],
    ],
];
