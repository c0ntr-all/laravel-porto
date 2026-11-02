<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Support;

class SteamWebApiUrl
{
    /**
     * @param  list<string>  $steamIds
     */
    public static function playerSummaries(array $steamIds): string
    {
        return self::build('player_summaries', [
            'key' => self::apiKey(),
            'steamids' => implode(',', $steamIds),
        ]);
    }

    public static function gameAchievements(int $appId, ?string $language = null): string
    {
        return self::build('game_achievements', [
            'key' => self::apiKey(),
            'appid' => $appId,
            'language' => $language ?? self::defaultLanguage(),
        ]);
    }

    public static function playerAchievements(int $appId, string $steamId, ?string $language = null): string
    {
        return self::build('player_achievements', [
            'appid' => $appId,
            'key' => self::apiKey(),
            'steamid' => $steamId,
            'l' => $language ?? self::defaultLanguage(),
        ]);
    }

    /**
     * @param  array<string, scalar|null>  $query
     */
    private static function build(string $pathKey, array $query): string
    {
        $base = rtrim((string) config('steam_web_api.base_url', 'https://api.steampowered.com'), '/');
        $defaults = [
            'player_summaries' => '/ISteamUser/GetPlayerSummaries/v0002/',
            'game_achievements' => '/IPlayerService/GetGameAchievements/v1/',
            'player_achievements' => '/ISteamUserStats/GetPlayerAchievements/v0001/',
        ];
        $path = (string) config('steam_web_api.paths.'.$pathKey, $defaults[$pathKey] ?? '/');
        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        $filtered = array_filter(
            $query,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        );

        return $base.$path.'?'.http_build_query($filtered);
    }

    private static function apiKey(): string
    {
        return (string) config('steam_web_api.key');
    }

    private static function defaultLanguage(): string
    {
        $language = trim((string) config('steam_web_api.language', 'russian'));

        return $language !== '' ? $language : 'russian';
    }
}
