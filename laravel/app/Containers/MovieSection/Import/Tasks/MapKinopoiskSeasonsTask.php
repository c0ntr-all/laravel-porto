<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Data\DTO\KinopoiskSeasonsResponseDto;
use App\Containers\MovieSection\Import\Data\DTO\ParsedSeasonDto;
use App\Containers\MovieSection\Import\Support\KinopoiskSeasonMapper;
use App\Ship\Parents\Tasks\Task as ParentTask;

class MapKinopoiskSeasonsTask extends ParentTask
{
    public function __construct(
        private readonly KinopoiskSeasonMapper $mapper,
    ) {
    }

    /**
     * @return list<ParsedSeasonDto>
     */
    public function run(KinopoiskSeasonsResponseDto $response): array
    {
        return $this->mapper->fromDocs($response->docs, $response->movie_kp_id);
    }
}
