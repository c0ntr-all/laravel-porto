<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Tasks;

use App\Containers\MovieSection\Season\Data\Repositories\SeasonRepository;
use App\Containers\MovieSection\Season\Models\Season;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteSeasonTask extends ParentTask
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
    ) {
    }

    public function run(Season $season): bool
    {
        return $this->seasonRepository->delete($season);
    }
}
