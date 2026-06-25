<?php

return [
    'permissions_policy' => env(
        'SECURITY_PERMISSIONS_POLICY',
        'camera=(), microphone=(), geolocation=(), payment=(self)'
    ),
    'csp' => [
        'enabled' => (bool) env('SECURITY_CSP_ENABLED', true),
        'report_only' => (bool) env('SECURITY_CSP_REPORT_ONLY', true),
        'report_uri' => env('SECURITY_CSP_REPORT_URI', '/csp/report'),
        'directives' => [
            'default-src' => ["'self'"],
            'base-uri' => ["'self'"],
            'object-src' => ["'none'"],
            'frame-ancestors' => ["'self'"],
            'img-src' => ["'self'", 'data:', 'https:'],
            'font-src' => ["'self'", 'https:', 'data:'],
            'style-src' => ["'self'", "'unsafe-inline'", 'https:'],
            'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'https:'],
            'connect-src' => ["'self'", 'https:'],
        ],
    ],
];
