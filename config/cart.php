<?php

return [
    'session_key' => env('CART_SESSION_KEY', 'cart.items'),
    'quantity' => [
        'min' => (int) env('CART_MIN_QTY', 1),
        'max' => (int) env('CART_MAX_QTY', 99),
    ],
    'shop' => [
        'per_page' => (int) env('SHOP_PRODUCTS_PER_PAGE', 12),
    ],
];
