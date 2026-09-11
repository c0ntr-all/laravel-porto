<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tasks;

use App\Containers\MovieSection\Movie\Data\Repositories\MovieRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Pagination\CursorPaginator;

class ListMoviesTask extends ParentTask
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
    ) {
    }

    public function run(): CursorPaginator
    {
        return $this->movieRepository->getWithCursor();
    }
}
