<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tasks;

use App\Containers\MovieSection\Movie\Data\DTO\MovieCreateData;
use App\Containers\MovieSection\Movie\Data\Repositories\MovieRepository;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateMovieTask extends ParentTask
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
    ) {
    }

    public function run(MovieCreateData $dto): Movie
    {
        return $this->movieRepository->create($dto);
    }
}
