<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Data\DTO\ReorderWidgetsDto;
use App\Containers\DashboardSection\Widget\Tasks\AttachWidgetPayloadsTask;
use App\Containers\DashboardSection\Widget\Tasks\ListWidgetsTask;
use App\Containers\DashboardSection\Widget\Tasks\ReorderWidgetsTask;
use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Containers\DashboardSection\Widget\UI\API\Requests\ReorderRequest;
use App\Containers\DashboardSection\Widget\UI\API\Transformers\WidgetTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class ReorderWidgetsAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::DASHBOARD_WIDGET;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly ReorderWidgetsTask $reorderWidgetsTask,
        private readonly ListWidgetsTask $listWidgetsTask,
        private readonly AttachWidgetPayloadsTask $attachWidgetPayloadsTask,
    ) {
        parent::__construct();
    }

    public function handle(Dashboard $dashboard, ReorderWidgetsDto $dto): void
    {
        $before = Widget::query()
            ->where('dashboard_id', $dashboard->id)
            ->whereIn('id', $dto->ids)
            ->pluck('sort_order', 'id');

        $this->reorderWidgetsTask->run($dashboard, $dto);

        $widgets = Widget::query()
            ->where('dashboard_id', $dashboard->id)
            ->whereIn('id', $dto->ids)
            ->get();

        foreach ($widgets as $widget) {
            if ((string) $before->get($widget->id) !== (string) $widget->sort_order) {
                $this->recordUseCase($widget);
            }
        }
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
