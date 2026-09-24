<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ParsedEpisodeDto extends Data
{
    public function __construct(
        public int $number,
        public ?int $kp_id = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $en_description = null,
        public ?int $duration = null,
        public ?string $air_date = null,
        public ?string $still = null,
        public ?string $still_preview = null,
    ) {
    }
}
