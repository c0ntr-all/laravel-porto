<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Dashboard\Tasks\GetDashboardTask;
use App\Containers\DashboardSection\Dashboard\UI\API\Requests\GetRequest;
use App\Containers\DashboardSection\Dashboard\UI\API\Transformers\DashboardTransformer;
use App\Containers\DashboardSection\Widget\Tasks\AttachWidgetPayloadsTask;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetDashboardAction extends BaseAction
{
    public function __construct(
        private readonly GetDashboardTask $getDashboardTask,
        private readonly AttachWidgetPayloadsTask $attachWidgetPayloadsTask,
    ) {
    }

    public function handle(Dashboard $dashboard): Dashboard
    {
        $dashboard = $this->getDashboardTask->run($dashboard, ['widgets']);
        $this->attachWidgetPayloadsTask->run($dashboard->widgets);

        return $dashboard;
    }

    public function asController(Dashboard $dashboard, GetRequest $request): JsonResponse
    {
        $dashboard = $this->handle($dashboard);

        return fractal($dashboard, new DashboardTransformer())
            ->withResourceName('dashboards')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
