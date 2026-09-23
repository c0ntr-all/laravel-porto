<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Tasks;

use App\Containers\MovieSection\Season\Data\Repositories\SeasonRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListSeasonsTask extends ParentTask
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
    ) {
    }

    public function run(): Collection
    {
        return $this->seasonRepository->get();
    }
}
