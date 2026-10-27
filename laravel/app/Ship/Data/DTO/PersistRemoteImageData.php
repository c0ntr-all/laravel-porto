<?php declare(strict_types=1);

namespace App\Ship\Data\DTO;

use App\Ship\Parents\DTO\Data;

class PersistRemoteImageData extends Data
{
    public function __construct(
        public string $url,
        public string $directory,
        public string $disk = 'public',
        public ?string $filename = null,
    ) {
    }
}
