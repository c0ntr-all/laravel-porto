<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Data\DTO;

use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Ship\Parents\DTO\Data;

class ParsedKinopoiskFilmDto extends Data
{
    /**
     * @param list<ParsedGenreDto> $genres
     * @param list<string> $countries
     * @param list<string> $parser_sources
     */
    public function __construct(
        public int $kp_id,
        public string $title,
        public int $year,
        public MovieTypeEnum $type,
        public ?string $description = null,
        public ?string $cover = null,
        public ?string $kp_img = null,
        public ?float $kp_rating = null,
        public array $genres = [],
        public array $countries = [],
        public string $source_url = '',
        public array $parser_sources = [],
    ) {
    }
}
