<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ParsedPersonDto extends Data
{
    public function __construct(
        public int $kp_id,
        public string $name,
        public string $en_profession,
        public ?string $en_name = null,
        public ?string $photo = null,
        public ?string $profession = null,
        public ?string $description = null,
    ) {
    }
}
