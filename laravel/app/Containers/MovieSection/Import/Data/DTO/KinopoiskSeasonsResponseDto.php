<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Data\DTO;

use App\Ship\Parents\DTO\Data;

class KinopoiskSeasonsResponseDto extends Data
{
    /**
     * @param list<array<string, mixed>> $docs
     * @param list<string> $page_urls
     */
    public function __construct(
        public int $movie_kp_id,
        public string $url,
        public int $http_status,
        public array $docs,
        public int $pages_fetched = 1,
        public array $page_urls = [],
    ) {
    }
}
