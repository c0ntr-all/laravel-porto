<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Contracts;

use App\Containers\GameSection\Steam\Data\DTO\SteamGetGameAchievementsResponseDto;
use App\Containers\GameSection\Steam\Data\DTO\SteamGetOwnedGamesResponseDto;
use App\Containers\GameSection\Steam\Data\DTO\SteamGetPlayerAchievementsResponseDto;
use App\Containers\GameSection\Steam\Data\DTO\SteamGetPlayerSummariesResponseDto;

interface SteamWebApiClientInterface
{
    /**
     * @param  list<string>  $steamIds
     */
    public function getPlayerSummaries(array $steamIds): SteamGetPlayerSummariesResponseDto;

    public function getGameAchievements(int $appId, ?string $language = null): SteamGetGameAchievementsResponseDto;

    public function getPlayerAchievements(
        int $appId,
        string $steamId,
        ?string $language = null,
    ): SteamGetPlayerAchievementsResponseDto;

    public function getOwnedGames(string $steamId): SteamGetOwnedGamesResponseDto;
}
