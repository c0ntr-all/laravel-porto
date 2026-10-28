<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Support;

class SteamStoreUrl
{
    public static function app(int $steamId): string
    {
        $base = rtrim((string) config('steam_store.base_url', 'https://store.steampowered.com'), '/');
        $path = sprintf((string) config('steam_store.app_path', '/app/%d'), $steamId);
        $locale = trim((string) config('steam_store.locale', 'english'));

        $url = $base.$path;
        if ($locale !== '') {
            $url .= '?l='.rawurlencode($locale);
        }

        return $url;
    }
}
