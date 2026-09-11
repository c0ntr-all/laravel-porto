<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tasks;

use App\Containers\MovieSection\Movie\Data\Repositories\MovieRepository;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SyncCountriesForMovieTask extends ParentTask
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
    ) {
    }

    /**
     * @param list<int> $countryIds
     */
    public function run(Movie $movie, array $countryIds): array
    {
        return $this->movieRepository->syncCountries($movie, $countryIds);
    }
}
