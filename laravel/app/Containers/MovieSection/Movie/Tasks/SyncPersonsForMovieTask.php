<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tasks;

use App\Containers\MovieSection\Movie\Data\Repositories\MovieRepository;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SyncPersonsForMovieTask extends ParentTask
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
    ) {
    }

    /**
     * @param list<array{person_id: int, profession_id: int, description: ?string}> $rows
     */
    public function run(Movie $movie, array $rows): void
    {
        $this->movieRepository->syncPersons($movie, $rows);
    }
}
