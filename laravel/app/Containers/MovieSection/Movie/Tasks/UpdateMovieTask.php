<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tasks;

use App\Containers\MovieSection\Movie\Data\DTO\MovieUpdateData;
use App\Containers\MovieSection\Movie\Data\Repositories\MovieRepository;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateMovieTask extends ParentTask
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
    ) {
    }

    public function run(Movie $movie, MovieUpdateData $dto): Movie
    {
        return $this->movieRepository->update($movie, $dto);
    }
}
