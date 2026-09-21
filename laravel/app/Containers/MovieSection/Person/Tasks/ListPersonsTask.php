<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\Tasks;

use App\Containers\MovieSection\Person\Data\Repositories\PersonRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Pagination\CursorPaginator;

class ListPersonsTask extends ParentTask
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    public function run(): CursorPaginator
    {
        return $this->personRepository->getWithCursor();
    }
}
