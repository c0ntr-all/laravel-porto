<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Tasks;

use App\Containers\GameSection\Import\Data\DTO\GameImportCreateData;
use App\Containers\GameSection\Import\Data\Repositories\GameImportRepository;
use App\Containers\GameSection\Import\Models\GameImport;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateGameImportLogTask extends ParentTask
{
    public function __construct(
        private readonly GameImportRepository $gameImportRepository,
    ) {}

    public function run(GameImportCreateData $dto): GameImport
    {
        return $this->gameImportRepository->create($dto);
    }
}
