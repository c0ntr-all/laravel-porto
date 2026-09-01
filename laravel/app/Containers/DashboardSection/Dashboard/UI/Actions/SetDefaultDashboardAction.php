<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Dashboard\Tasks\SetDefaultDashboardTask;
use App\Containers\DashboardSection\Dashboard\UI\API\Requests\SetDefaultRequest;
use App\Containers\DashboardSection\Dashboard\UI\API\Transformers\DashboardTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class SetDefaultDashboardAction extends BaseAction
{
    public function __construct(
        private readonly SetDefaultDashboardTask $setDefaultDashboardTask,
    ) {
    }

    public function handle(Dashboard $dashboard): Dashboard
    {
        return $this->setDefaultDashboardTask->run($dashboard)->load('widgets');
    }

    public function asController(Dashboard $dashboard, SetDefaultRequest $request): JsonResponse
    {
        $dashboard = $this->handle($dashboard);

        return fractal($dashboard, new DashboardTransformer())
            ->withResourceName('dashboards')
            ->addMeta(['message' => 'Dashboard set as default!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
