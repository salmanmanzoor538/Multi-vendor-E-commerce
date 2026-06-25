<?php

return [
    'status_labels' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],

    'pagination' => [
        'admin_per_page' => env('VENDOR_ADMIN_PER_PAGE', 15),
        'products_per_page' => env('VENDOR_PRODUCTS_PER_PAGE', 15),
    ],

    'routes' => [
        'registration' => 'vendor.register.create',
        'dashboard' => 'vendor.dashboard',
    ],
];
