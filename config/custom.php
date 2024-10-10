<?php

return [
    'base_url' => env('APP_URL'),

    'environments' => [
        'local' => [
            'base_url' => 'https://localhost:3000',
        ],
        'testing' => [
            'base_url' => 'https://test.v2.alquranclasses.com',
        ],
        'beta' => [
            'base_url' => 'https://beta.v2.alquranclasses.com',
        ],
        'staging' => [
            'base_url' => 'https://staging.v2.alquranclasses.com',
        ],
    ],
];
