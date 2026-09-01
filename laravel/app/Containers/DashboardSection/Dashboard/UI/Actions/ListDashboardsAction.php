<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Tasks\EnsureDefaultDashboardTask;
use App\Containers\DashboardSection\Dashboard\Tasks\ListDashboardsTask;
use App\Containers\DashboardSection\Dashboard\UI\API\Requests\ListRequest;
use App\Containers\DashboardSection\Dashboard\UI\API\Transformers\DashboardTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListDashboardsAction extends BaseAction
{
    public function __construct(
        private readonly ListDashboardsTask $listDashboardsTask,
        private readonly EnsureDefaultDashboardTask $ensureDefaultDashboardTask,
    ) {
    }

    public function handle(): Collection
    {
        $this->ensureDefaultDashboardTask->run((int) auth()->id());

        return $this->listDashboardsTask->run(['widgets']);
    }

    public function asController(ListRequest $request): JsonResponse
    {
        $dashboards = $this->handle();

        return fractal($dashboards, new DashboardTransformer())
            ->withResourceName('dashboards')
            ->addMeta(['count' => $dashboards->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
