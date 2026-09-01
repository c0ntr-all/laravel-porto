<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Containers\DashboardSection\Widget\Tasks\AttachWidgetPayloadsTask;
use App\Containers\DashboardSection\Widget\UI\API\Requests\GetPayloadRequest;
use App\Containers\DashboardSection\Widget\UI\API\Transformers\WidgetTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetWidgetPayloadAction extends BaseAction
{
    public function __construct(
        private readonly AttachWidgetPayloadsTask $attachWidgetPayloadsTask,
    ) {
    }

    public function handle(Widget $widget): Widget
    {
        $this->attachWidgetPayloadsTask->run(collect([$widget]));

        return $widget;
    }

    public function asController(Dashboard $dashboard, Widget $widget, GetPayloadRequest $request): JsonResponse
    {
        abort_unless($widget->dashboard_id === $dashboard->id, 404);

        $widget = $this->handle($widget);

        return fractal($widget, new WidgetTransformer())
            ->withResourceName('dashboard_widgets')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
