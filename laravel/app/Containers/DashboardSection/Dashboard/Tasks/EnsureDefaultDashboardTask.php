<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\Tasks;

use App\Containers\DashboardSection\Dashboard\Data\DTO\CreateDashboardDto;
use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Tasks\SeedDefaultWidgetsTask;
use App\Ship\Parents\Tasks\Task as ParentTask;

class EnsureDefaultDashboardTask extends ParentTask
{
    public function __construct(
        private readonly ListDashboardsTask $listDashboardsTask,
        private readonly CreateDashboardTask $createDashboardTask,
        private readonly SeedDefaultWidgetsTask $seedDefaultWidgetsTask,
    ) {
    }

    public function run(int $userId): Dashboard
    {
        $dashboards = $this->listDashboardsTask->run(['widgets']);

        if ($dashboards->isNotEmpty()) {
            return $dashboards->firstWhere('is_default', true) ?? $dashboards->first();
        }

        $dashboard = $this->createDashboardTask->run(CreateDashboardDto::from([
            'user_id' => $userId,
            'name' => (string) config('dashboard.default_name', 'Главная'),
            'is_default' => true,
            'sort_order' => 0,
        ]));

        return $this->seedDefaultWidgetsTask->run($dashboard);
    }
}
