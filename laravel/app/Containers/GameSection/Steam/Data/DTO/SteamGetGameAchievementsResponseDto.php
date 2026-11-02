<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Data\DTO;

use App\Ship\Parents\DTO\Data;

class SteamGetGameAchievementsResponseDto extends Data
{
    /**
     * @param  list<SteamGameAchievementDto>  $achievements
     */
    public function __construct(
        public int $app_id,
        public string $url,
        public int $http_status,
        public array $achievements,
    ) {}
}
