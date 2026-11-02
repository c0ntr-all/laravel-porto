<?php

declare(strict_types=1);

namespace App\Containers\AppSection\Steam\Contracts;

use App\Containers\AppSection\Steam\Data\DTO\SteamGetPlayerSummariesResponseDto;

interface SteamWebApiClientInterface
{
    /**
     * @param  list<string>  $steamIds
     */
    public function getPlayerSummaries(array $steamIds): SteamGetPlayerSummariesResponseDto;
}
