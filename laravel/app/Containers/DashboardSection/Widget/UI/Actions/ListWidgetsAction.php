<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Tasks\AttachWidgetPayloadsTask;
use App\Containers\DashboardSection\Widget\Tasks\ListWidgetsTask;
use App\Containers\DashboardSection\Widget\UI\API\Requests\ListRequest;
use App\Containers\DashboardSection\Widget\UI\API\Transformers\WidgetTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListWidgetsAction extends BaseAction
{
    public function __construct(
        private readonly ListWidgetsTask $listWidgetsTask,
        private readonly AttachWidgetPayloadsTask $attachWidgetPayloadsTask,
    ) {
    }

    public function handle(Dashboard $dashboard): Collection
    {
        $widgets = $this->listWidgetsTask->run($dashboard);

        return $this->attachWidgetPayloadsTask->run($widgets);
    }

    public function asController(Dashboard $dashboard, ListRequest $request): JsonResponse
    {
        $widgets = $this->handle($dashboard);

        return fractal($widgets, new WidgetTransformer())
            ->withResourceName('dashboard_widgets')
            ->addMeta(['count' => $widgets->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
