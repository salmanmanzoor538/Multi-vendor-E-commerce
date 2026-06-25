<?php

return [
    'seed_users' => [
        'admin' => [
            'name' => env('SEED_ADMIN_NAME', 'Platform Admin'),
            'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
            'password' => env('SEED_ADMIN_PASSWORD', 'password'),
        ],
        'vendor' => [
            'name' => env('SEED_VENDOR_NAME', 'Default Vendor'),
            'email' => env('SEED_VENDOR_EMAIL', 'vendor@example.com'),
            'password' => env('SEED_VENDOR_PASSWORD', 'password'),
        ],
        'customer' => [
            'name' => env('SEED_CUSTOMER_NAME', 'Default Customer'),
            'email' => env('SEED_CUSTOMER_EMAIL', 'customer@example.com'),
            'password' => env('SEED_CUSTOMER_PASSWORD', 'password'),
        ],
    ],
];
