<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tasks;

use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Collection;

class AttachWidgetPayloadsTask extends ParentTask
{
    public function __construct(
        private readonly ResolveWidgetPayloadTask $resolveWidgetPayloadTask,
    ) {
    }

    public function run(Collection $widgets): Collection
    {
        $widgets->each(function (Widget $widget) {
            $widget->resolvedPayload = $this->resolveWidgetPayloadTask->run($widget)->toArray();
        });

        return $widgets;
    }
}
