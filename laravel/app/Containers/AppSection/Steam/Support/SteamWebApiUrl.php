<?php

declare(strict_types=1);

namespace App\Containers\AppSection\Steam\Support;

class SteamWebApiUrl
{
    /**
     * @param  list<string>  $steamIds
     */
    public static function playerSummaries(array $steamIds): string
    {
        $base = rtrim((string) config('steam_web_api.base_url', 'https://api.steampowered.com'), '/');
        $path = (string) config('steam_web_api.paths.player_summaries', '/ISteamUser/GetPlayerSummaries/v0002/');
        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        $query = http_build_query([
            'key' => (string) config('steam_web_api.key'),
            'steamids' => implode(',', $steamIds),
        ]);

        return $base.$path.'?'.$query;
    }
}
