<?php

return [
    'url' => env('API_URL', 'http://localhost'),
    'prefix' => env('API_PREFIX', ''),
    'throttle' => [
        'enabled' => env('GLOBAL_API_RATE_LIMIT_ENABLED', true),
        'attempts' => env('GLOBAL_API_RATE_LIMIT_ATTEMPTS_PER_MIN', '30'),
        'expires' => env('GLOBAL_API_RATE_LIMIT_EXPIRES_IN_MIN', '1'),
    ],
];
