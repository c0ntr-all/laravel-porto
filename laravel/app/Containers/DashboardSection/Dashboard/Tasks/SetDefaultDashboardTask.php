<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\Tasks;

use App\Containers\DashboardSection\Dashboard\Data\DTO\UpdateDashboardDto;
use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SetDefaultDashboardTask extends ParentTask
{
    public function __construct(
        private readonly UpdateDashboardTask $updateDashboardTask,
    ) {
    }

    public function run(Dashboard $dashboard): Dashboard
    {
        $dto = UpdateDashboardDto::from(['is_default' => true]);

        return $this->updateDashboardTask->run($dashboard, $dto);
    }
}
