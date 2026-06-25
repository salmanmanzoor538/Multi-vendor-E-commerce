<?php

return [
    'statuses' => [
        'active',
        'inactive',
    ],

    'pagination' => [
        'attributes_per_page' => env('ATTRIBUTES_PER_PAGE', 15),
    ],
];
