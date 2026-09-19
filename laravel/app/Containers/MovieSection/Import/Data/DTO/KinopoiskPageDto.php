<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Data\DTO;

use App\Ship\Parents\DTO\Data;

class KinopoiskPageDto extends Data
{
    public function __construct(
        public int $kp_id,
        public string $url,
        public int $http_status,
        public string $html,
        public ?string $final_url = null,
        public ?string $content_type = null,
        /** @var array<string, mixed> */
        public array $headers = [],
        /** @var list<array<string, mixed>> */
        public array $attempts = [],
    ) {
    }
}
