<?php declare(strict_types=1);

return [
    'base_url' => env('KINOPOISK_DEV_API_URL', 'https://api.poiskkino.dev'),
    'movie_path' => env('KINOPOISK_DEV_MOVIE_PATH', '/v1.5/movie/%d'),
    'season_path' => env('KINOPOISK_DEV_SEASON_PATH', '/v1.5/season'),
    'season_page_limit' => (int) env('KINOPOISK_DEV_SEASON_PAGE_LIMIT', 250),
    'token' => env('KINOPOISK_DEV_API_KEY'),
    'timeout' => (int) env('KINOPOISK_DEV_TIMEOUT', 30),
    'connect_timeout' => (int) env('KINOPOISK_DEV_CONNECT_TIMEOUT', 15),
    'retries' => (int) env('KINOPOISK_DEV_RETRIES', 3),
    'retry_sleep_ms' => (int) env('KINOPOISK_DEV_RETRY_SLEEP_MS', 400),
];
