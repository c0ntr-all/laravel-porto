<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Data\Repositories\MovieImportRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Pagination\CursorPaginator;

class ListMovieImportsTask extends ParentTask
{
    public function __construct(
        private readonly MovieImportRepository $movieImportRepository,
    ) {
    }

    public function run(int $userId): CursorPaginator
    {
        return $this->movieImportRepository->getWithCursor($userId);
    }
}
