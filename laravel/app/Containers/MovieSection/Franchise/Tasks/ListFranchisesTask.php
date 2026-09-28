<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\Tasks;

use App\Containers\MovieSection\Franchise\Data\Repositories\FranchiseRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListFranchisesTask extends ParentTask
{
    public function __construct(
        private readonly FranchiseRepository $franchiseRepository,
    ) {
    }

    public function run(): Collection
    {
        return $this->franchiseRepository->get();
    }
}
