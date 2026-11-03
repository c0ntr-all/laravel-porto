<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Data\DTO;

use App\Ship\Parents\DTO\Data;

class SteamGetOwnedGamesResponseDto extends Data
{
    /**
     * @param  list<SteamOwnedGameDto>  $games
     */
    public function __construct(
        public string $steam_id,
        public string $url,
        public int $http_status,
        public int $game_count,
        public array $games,
    ) {}
}
