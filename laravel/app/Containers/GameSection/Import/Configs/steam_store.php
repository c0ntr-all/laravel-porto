<?php

declare(strict_types=1);

return [
    'base_url' => env('STEAM_STORE_URL', 'https://store.steampowered.com'),
    'app_path' => '/app/%d',
    'locale' => env('STEAM_STORE_LOCALE', 'english'),
    'timeout' => (int) env('STEAM_STORE_TIMEOUT', 30),
    'connect_timeout' => (int) env('STEAM_STORE_CONNECT_TIMEOUT', 15),
    'retries' => (int) env('STEAM_STORE_RETRIES', 2),
    'retry_sleep_ms' => (int) env('STEAM_STORE_RETRY_SLEEP_MS', 400),
    'user_agent' => env(
        'STEAM_STORE_USER_AGENT',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    ),
];
