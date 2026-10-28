<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Tasks;

use App\Containers\GameSection\Import\Data\Repositories\GameImportRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Pagination\CursorPaginator;

class ListGameImportsTask extends ParentTask
{
    public function __construct(
        private readonly GameImportRepository $gameImportRepository,
    ) {}

    public function run(int $userId): CursorPaginator
    {
        return $this->gameImportRepository->getWithCursor($userId);
    }
}
