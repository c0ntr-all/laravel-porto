<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Exceptions;

class SteamWebApiPlayerAchievementsException extends SteamWebApiException
{
    public function __construct(int $appId, string $steamId)
    {
        parent::__construct(
            'Steam player achievements for app '.$appId.' and steam id '.$steamId.' are unavailable.',
            422,
        );
    }
}
