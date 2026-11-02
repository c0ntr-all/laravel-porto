<?php

declare(strict_types=1);

namespace App\Containers\AppSection\Steam\Tasks;

use App\Containers\AppSection\Steam\Data\DTO\SteamPlayerSummaryDto;
use App\Containers\AppSection\Steam\Exceptions\SteamWebApiPlayerNotFoundException;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FetchSteamPlayerSummaryTask extends ParentTask
{
    public function __construct(
        private readonly FetchSteamPlayerSummariesTask $fetchSteamPlayerSummariesTask,
    ) {}

    public function run(string $steamId): SteamPlayerSummaryDto
    {
        $response = $this->fetchSteamPlayerSummariesTask->run($steamId);
        $player = $response->findBySteamId($steamId) ?? $response->first();

        if ($player === null) {
            throw new SteamWebApiPlayerNotFoundException($steamId);
        }

        return $player;
    }
}
