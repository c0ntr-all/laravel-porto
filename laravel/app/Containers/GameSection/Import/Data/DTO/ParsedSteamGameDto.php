<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ParsedSteamGameDto extends Data
{
    public function __construct(
        public int $steam_id,
        public string $name,
        public ?string $description = null,
        public ?string $img = null,
        public ?string $air_date = null,
        public ?string $developer = null,
        public ?string $publisher = null,
        public string $source_url = '',
    ) {}
}
