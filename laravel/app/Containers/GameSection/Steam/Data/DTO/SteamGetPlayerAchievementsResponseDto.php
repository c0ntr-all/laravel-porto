<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Data\DTO;

use App\Ship\Parents\DTO\Data;

class SteamGetPlayerAchievementsResponseDto extends Data
{
    /**
     * @param  list<SteamPlayerAchievementDto>  $achievements
     */
    public function __construct(
        public int $app_id,
        public string $steam_id,
        public string $url,
        public int $http_status,
        public bool $success,
        public ?string $game_name,
        public array $achievements,
    ) {}
}
