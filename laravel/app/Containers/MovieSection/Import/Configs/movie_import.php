<?php declare(strict_types=1);

return [
    'base_url' => env('KINOPOISK_BASE_URL', 'https://www.kinopoisk.ru'),
    'timeout' => (int) env('KINOPOISK_TIMEOUT', 20),
    'connect_timeout' => (int) env('KINOPOISK_CONNECT_TIMEOUT', 5),
    'cookie' => env('KINOPOISK_COOKIE'),
    'user_agent' => env(
        'KINOPOISK_USER_AGENT',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
    ),
    'paths' => ['film', 'series'],
];
