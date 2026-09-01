<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tasks;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Data\DTO\CreateWidgetDto;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Managers\WidgetRegistry;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SeedDefaultWidgetsTask extends ParentTask
{
    public function __construct(
        private readonly WidgetRegistry $widgetRegistry,
        private readonly CreateWidgetTask $createWidgetTask,
        private readonly ValidateWidgetConfigTask $validateWidgetConfigTask,
    ) {
    }

    public function run(Dashboard $dashboard): Dashboard
    {
        $presets = config('dashboard.default_widgets', []);

        foreach ($presets as $index => $preset) {
            $type = (string) ($preset['type'] ?? '');

            if ($type === '' || !$this->widgetRegistry->has($type)) {
                continue;
            }

            $definition = $this->widgetRegistry->get($type);
            $size = WidgetSizeEnum::tryFrom((string) ($preset['size'] ?? '')) ?? $definition->defaultSize();
            $config = is_array($preset['config'] ?? null) ? $preset['config'] : [];

            $dto = CreateWidgetDto::from([
                'dashboard_id' => $dashboard->id,
                'user_id' => $dashboard->user_id,
                'type' => $type,
                'size' => $size,
                'sort_order' => $index,
                'config' => $this->validateWidgetConfigTask->run($definition, $config, $size),
                'title' => $preset['title'] ?? null,
            ]);

            $this->createWidgetTask->run($dto);
        }

        return $dashboard->load('widgets');
    }
}
