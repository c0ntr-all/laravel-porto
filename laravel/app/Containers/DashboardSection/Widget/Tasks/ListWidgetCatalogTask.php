<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tasks;

use App\Containers\DashboardSection\Widget\Managers\WidgetRegistry;
use App\Ship\Parents\Tasks\Task as ParentTask;

class ListWidgetCatalogTask extends ParentTask
{
    public function __construct(
        private readonly WidgetRegistry $widgetRegistry,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function run(): array
    {
        return $this->widgetRegistry->catalog();
    }
}
