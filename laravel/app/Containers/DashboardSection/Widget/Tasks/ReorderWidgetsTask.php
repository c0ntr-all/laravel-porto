<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tasks;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Data\DTO\ReorderWidgetsDto;
use App\Containers\DashboardSection\Widget\Data\Repositories\WidgetRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;

class ReorderWidgetsTask extends ParentTask
{
    public function __construct(
        private readonly WidgetRepository $widgetRepository,
    ) {
    }

    public function run(Dashboard $dashboard, ReorderWidgetsDto $dto): void
    {
        $this->widgetRepository->reorder($dashboard, $dto->ids);
    }
}
