<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Tasks;

use App\Containers\GameSection\Steam\Contracts\SteamWebApiClientInterface;
use App\Containers\GameSection\Steam\Data\DTO\SteamGetOwnedGamesResponseDto;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FetchSteamOwnedGamesTask extends ParentTask
{
    public function __construct(
        private readonly SteamWebApiClientInterface $steamWebApiClient,
    ) {}

    public function run(string $steamId): SteamGetOwnedGamesResponseDto
    {
        return $this->steamWebApiClient->getOwnedGames($steamId);
    }
}
