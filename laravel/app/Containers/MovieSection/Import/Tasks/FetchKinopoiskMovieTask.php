<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Contracts\KinopoiskMovieApiClientInterface;
use App\Containers\MovieSection\Import\Data\DTO\KinopoiskApiResponseDto;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FetchKinopoiskMovieTask extends ParentTask
{
    public function __construct(
        private readonly KinopoiskMovieApiClientInterface $client,
    ) {
    }

    public function run(int $kpId): KinopoiskApiResponseDto
    {
        return $this->client->fetch($kpId);
    }
}
