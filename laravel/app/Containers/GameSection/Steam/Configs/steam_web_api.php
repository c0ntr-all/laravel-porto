<?php

declare(strict_types=1);

return [
    'base_url' => env('STEAM_WEB_API_URL', 'https://api.steampowered.com'),
    'key' => env('STEAM_WEB_API_KEY'),
    'language' => env('STEAM_WEB_API_LANGUAGE', 'russian'),
    'timeout' => (int) env('STEAM_WEB_API_TIMEOUT', 30),
    'connect_timeout' => (int) env('STEAM_WEB_API_CONNECT_TIMEOUT', 15),
    'retries' => (int) env('STEAM_WEB_API_RETRIES', 2),
    'retry_sleep_ms' => (int) env('STEAM_WEB_API_RETRY_SLEEP_MS', 400),
    'paths' => [
        'player_summaries' => '/ISteamUser/GetPlayerSummaries/v0002/',
        'game_achievements' => '/IPlayerService/GetGameAchievements/v1/',
        'player_achievements' => '/ISteamUserStats/GetPlayerAchievements/v0001/',
    ],
];
