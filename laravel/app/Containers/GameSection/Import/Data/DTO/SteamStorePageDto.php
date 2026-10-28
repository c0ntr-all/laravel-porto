<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Data\DTO;

use App\Ship\Parents\DTO\Data;

class SteamStorePageDto extends Data
{
    public function __construct(
        public int $steam_id,
        public string $url,
        public int $http_status,
        public string $html,
    ) {}
}
