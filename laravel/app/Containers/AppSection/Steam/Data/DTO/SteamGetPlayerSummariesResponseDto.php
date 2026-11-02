<?php

declare(strict_types=1);

namespace App\Containers\AppSection\Steam\Data\DTO;

use App\Ship\Parents\DTO\Data;

class SteamGetPlayerSummariesResponseDto extends Data
{
    /**
     * @param  list<SteamPlayerSummaryDto>  $players
     */
    public function __construct(
        public string $url,
        public int $http_status,
        public array $players,
    ) {}

    public function first(): ?SteamPlayerSummaryDto
    {
        return $this->players[0] ?? null;
    }

    public function findBySteamId(string $steamId): ?SteamPlayerSummaryDto
    {
        foreach ($this->players as $player) {
            if ($player->steam_id === $steamId) {
                return $player;
            }
        }

        return null;
    }
}
