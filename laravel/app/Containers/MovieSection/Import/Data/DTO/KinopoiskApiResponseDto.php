<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Data\DTO;

use App\Ship\Parents\DTO\Data;

class KinopoiskApiResponseDto extends Data
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $headers
     */
    public function __construct(
        public int $kp_id,
        public string $url,
        public int $http_status,
        public array $payload,
        public string $body,
        public array $headers = [],
    ) {
    }
}
