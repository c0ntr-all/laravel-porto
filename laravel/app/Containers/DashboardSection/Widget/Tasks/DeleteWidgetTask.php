<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tasks;

use App\Containers\DashboardSection\Widget\Data\Repositories\WidgetRepository;
use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteWidgetTask extends ParentTask
{
    public function __construct(
        private readonly WidgetRepository $widgetRepository,
    ) {
    }

    public function run(Widget $widget): bool
    {
        return $this->widgetRepository->delete($widget);
    }
}
