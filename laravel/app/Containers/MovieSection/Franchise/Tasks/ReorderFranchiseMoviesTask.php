<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\Tasks;

use App\Containers\MovieSection\Franchise\Data\DTO\ReorderFranchiseMoviesData;
use App\Containers\MovieSection\Franchise\Data\Repositories\FranchiseRepository;
use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Ship\Parents\Tasks\Task as ParentTask;

class ReorderFranchiseMoviesTask extends ParentTask
{
    public function __construct(
        private readonly FranchiseRepository $franchiseRepository,
    ) {
    }

    public function run(Franchise $franchise, ReorderFranchiseMoviesData $dto): Franchise
    {
        return $this->franchiseRepository->reorderMovies($franchise, $dto->movie_ids);
    }
}
