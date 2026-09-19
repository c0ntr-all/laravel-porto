<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Contracts\KinopoiskFilmPageClientInterface;
use App\Containers\MovieSection\Import\Data\DTO\KinopoiskPageDto;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FetchKinopoiskFilmPageTask extends ParentTask
{
    public function __construct(
        private readonly KinopoiskFilmPageClientInterface $kinopoiskFilmPageClient,
    ) {
    }

    public function run(int $kpId): KinopoiskPageDto
    {
        return $this->kinopoiskFilmPageClient->fetch($kpId);
    }
}
