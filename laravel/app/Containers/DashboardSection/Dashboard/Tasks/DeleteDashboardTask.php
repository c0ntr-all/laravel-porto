<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\Tasks;

use App\Containers\DashboardSection\Dashboard\Data\Repositories\DashboardRepository;
use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteDashboardTask extends ParentTask
{
    public function __construct(
        private readonly DashboardRepository $dashboardRepository,
    ) {
    }

    public function run(Dashboard $dashboard): bool
    {
        return $this->dashboardRepository->delete($dashboard);
    }
}
