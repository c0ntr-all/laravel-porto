<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\Tasks;

use App\Containers\DashboardSection\Dashboard\Data\Repositories\DashboardRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListDashboardsTask extends ParentTask
{
    public function __construct(
        private readonly DashboardRepository $dashboardRepository,
    ) {
    }

    /**
     * @param list<string> $with
     */
    public function run(array $with = []): Collection
    {
        return $this->dashboardRepository->list($with);
    }
}
