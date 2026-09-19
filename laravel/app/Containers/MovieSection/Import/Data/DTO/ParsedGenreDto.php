<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ParsedGenreDto extends Data
{
    public function __construct(
        public string $name,
        public ?int $kp_id = null,
    ) {
    }
}
