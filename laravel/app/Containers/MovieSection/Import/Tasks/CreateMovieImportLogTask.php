<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Data\DTO\MovieImportCreateData;
use App\Containers\MovieSection\Import\Data\Repositories\MovieImportRepository;
use App\Containers\MovieSection\Import\Models\MovieImport;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateMovieImportLogTask extends ParentTask
{
    public function __construct(
        private readonly MovieImportRepository $movieImportRepository,
    ) {
    }

    public function run(MovieImportCreateData $dto): MovieImport
    {
        return $this->movieImportRepository->create($dto);
    }
}
