<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Tasks;

use App\Containers\GameSection\Steam\Contracts\SteamWebApiClientInterface;
use App\Containers\GameSection\Steam\Data\DTO\SteamGetPlayerSummariesResponseDto;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FetchSteamPlayerSummariesTask extends ParentTask
{
    public function __construct(
        private readonly SteamWebApiClientInterface $steamWebApiClient,
    ) {}

    /**
     * @param  list<string>|string  $steamIds
     */
    public function run(array|string $steamIds): SteamGetPlayerSummariesResponseDto
    {
        $ids = is_array($steamIds) ? $steamIds : [$steamIds];

        return $this->steamWebApiClient->getPlayerSummaries($ids);
    }
}
