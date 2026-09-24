<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Contracts\KinopoiskMovieApiClientInterface;
use App\Containers\MovieSection\Import\Data\DTO\KinopoiskSeasonsResponseDto;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FetchKinopoiskSeasonsTask extends ParentTask
{
    public function __construct(
        private readonly KinopoiskMovieApiClientInterface $client,
    ) {
    }

    public function run(int $movieKpId): KinopoiskSeasonsResponseDto
    {
        return $this->client->fetchSeasonsByMovieId($movieKpId);
    }
}
