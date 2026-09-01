<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Data\DTO\ReorderWidgetsDto;
use App\Containers\DashboardSection\Widget\Tasks\AttachWidgetPayloadsTask;
use App\Containers\DashboardSection\Widget\Tasks\ListWidgetsTask;
use App\Containers\DashboardSection\Widget\Tasks\ReorderWidgetsTask;
use App\Containers\DashboardSection\Widget\UI\API\Requests\ReorderRequest;
use App\Containers\DashboardSection\Widget\UI\API\Transformers\WidgetTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ReorderWidgetsAction extends BaseAction
{
    public function __construct(
        private readonly ReorderWidgetsTask $reorderWidgetsTask,
        private readonly ListWidgetsTask $listWidgetsTask,
        private readonly AttachWidgetPayloadsTask $attachWidgetPayloadsTask,
    ) {
    }

    public function handle(Dashboard $dashboard, ReorderWidgetsDto $dto): void
    {
        $this->reorderWidgetsTask->run($dashboard, $dto);
    }

    public function asController(Dashboard $dashboard, ReorderRequest $request): JsonResponse
    {
        $this->handle($dashboard, ReorderWidgetsDto::from($request->validated()));

        $widgets = $this->attachWidgetPayloadsTask->run($this->listWidgetsTask->run($dashboard));

        return fractal($widgets, new WidgetTransformer())
            ->withResourceName('dashboard_widgets')
            ->addMeta(['message' => 'Widgets successfully reordered!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
