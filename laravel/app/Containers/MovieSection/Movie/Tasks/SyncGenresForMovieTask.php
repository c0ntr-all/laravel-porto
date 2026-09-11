<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tasks;

use App\Containers\MovieSection\Movie\Data\Repositories\MovieRepository;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SyncGenresForMovieTask extends ParentTask
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
    ) {
    }

    /**
     * @param list<int> $genreIds
     */
    public function run(Movie $movie, array $genreIds): array
    {
        return $this->movieRepository->syncGenres($movie, $genreIds);
    }
}
