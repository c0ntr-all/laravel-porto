<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ParsedSeasonDto extends Data
{
    /**
     * @param list<ParsedEpisodeDto> $episodes
     */
    public function __construct(
        public int $number,
        public int $kp_movie_id,
        public ?int $kp_id = null,
        public ?int $kp_season_id = null,
        public ?string $name = null,
        public ?string $en_name = null,
        public ?string $air_date = null,
        public ?int $episodes_count = null,
        public ?int $duration = null,
        public ?string $poster = null,
        public ?string $poster_preview = null,
        public array $episodes = [],
    ) {
    }
}
