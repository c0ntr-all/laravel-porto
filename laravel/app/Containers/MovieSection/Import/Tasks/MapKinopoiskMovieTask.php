<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Data\DTO\KinopoiskApiResponseDto;
use App\Containers\MovieSection\Import\Data\DTO\ParsedKinopoiskFilmDto;
use App\Containers\MovieSection\Import\Support\KinopoiskMovieMapper;
use App\Ship\Parents\Tasks\Task as ParentTask;

class MapKinopoiskMovieTask extends ParentTask
{
    public function __construct(
        private readonly KinopoiskMovieMapper $mapper,
    ) {
    }

    public function run(KinopoiskApiResponseDto $response): ParsedKinopoiskFilmDto
    {
        return $this->mapper->fromApi($response->payload, $response->kp_id, $response->url);
    }
}
