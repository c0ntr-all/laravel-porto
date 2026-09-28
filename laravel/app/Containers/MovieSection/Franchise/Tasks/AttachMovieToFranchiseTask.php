<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\Tasks;

use App\Containers\MovieSection\Franchise\Data\Repositories\FranchiseRepository;
use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;

class AttachMovieToFranchiseTask extends ParentTask
{
    public function __construct(
        private readonly FranchiseRepository $franchiseRepository,
    ) {
    }

    public function run(Franchise $franchise, Movie $movie, ?int $order = null): Franchise
    {
        return $this->franchiseRepository->attachMovie($franchise, $movie, $order);
    }
}
