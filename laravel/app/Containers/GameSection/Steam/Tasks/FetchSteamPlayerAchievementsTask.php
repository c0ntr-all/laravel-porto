<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Tasks;

use App\Containers\GameSection\Steam\Contracts\SteamWebApiClientInterface;
use App\Containers\GameSection\Steam\Data\DTO\SteamGetPlayerAchievementsResponseDto;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FetchSteamPlayerAchievementsTask extends ParentTask
{
    public function __construct(
        private readonly SteamWebApiClientInterface $steamWebApiClient,
    ) {}

    public function run(
        int $appId,
        string $steamId,
        ?string $language = null,
    ): SteamGetPlayerAchievementsResponseDto {
        return $this->steamWebApiClient->getPlayerAchievements($appId, $steamId, $language);
    }
}
