<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Tasks;

use App\Containers\GameSection\Steam\Contracts\SteamWebApiClientInterface;
use App\Containers\GameSection\Steam\Data\DTO\SteamGetGameAchievementsResponseDto;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FetchSteamGameAchievementsTask extends ParentTask
{
    public function __construct(
        private readonly SteamWebApiClientInterface $steamWebApiClient,
    ) {}

    public function run(int $appId, ?string $language = null): SteamGetGameAchievementsResponseDto
    {
        return $this->steamWebApiClient->getGameAchievements($appId, $language);
    }
}
