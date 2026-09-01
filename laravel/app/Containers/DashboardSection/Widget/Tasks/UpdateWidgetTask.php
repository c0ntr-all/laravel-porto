<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tasks;

use App\Containers\DashboardSection\Widget\Data\DTO\UpdateWidgetDto;
use App\Containers\DashboardSection\Widget\Data\Repositories\WidgetRepository;
use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateWidgetTask extends ParentTask
{
    public function __construct(
        private readonly WidgetRepository $widgetRepository,
    ) {
    }

    public function run(Widget $widget, UpdateWidgetDto $dto): Widget
    {
        return $this->widgetRepository->update($widget, $dto);
    }
}
