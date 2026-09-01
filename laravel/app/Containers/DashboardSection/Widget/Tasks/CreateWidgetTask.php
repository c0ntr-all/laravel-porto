<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tasks;

use App\Containers\DashboardSection\Widget\Data\DTO\CreateWidgetDto;
use App\Containers\DashboardSection\Widget\Data\Repositories\WidgetRepository;
use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateWidgetTask extends ParentTask
{
    public function __construct(
        private readonly WidgetRepository $widgetRepository,
    ) {
    }

    public function run(CreateWidgetDto $dto): Widget
    {
        return $this->widgetRepository->create($dto);
    }
}
