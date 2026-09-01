<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Data\DTO\CreateDashboardDto;
use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Dashboard\Tasks\CreateDashboardTask;
use App\Containers\DashboardSection\Dashboard\UI\API\Requests\CreateRequest;
use App\Containers\DashboardSection\Dashboard\UI\API\Transformers\DashboardTransformer;
use App\Containers\DashboardSection\Widget\Tasks\AttachWidgetPayloadsTask;
use App\Containers\DashboardSection\Widget\Tasks\SeedDefaultWidgetsTask;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreateDashboardAction extends BaseAction
{
    public function __construct(
        private readonly CreateDashboardTask $createDashboardTask,
        private readonly SeedDefaultWidgetsTask $seedDefaultWidgetsTask,
        private readonly AttachWidgetPayloadsTask $attachWidgetPayloadsTask,
    ) {
    }

    public function handle(CreateDashboardDto $dto): Dashboard
    {
        return DB::transaction(function () use ($dto) {
            $dashboard = $this->createDashboardTask->run($dto);

            if ($dto->with_defaults) {
                $dashboard = $this->seedDefaultWidgetsTask->run($dashboard);
            } else {
                $dashboard->load('widgets');
            }

            $this->attachWidgetPayloadsTask->run($dashboard->widgets);

            return $dashboard;
        });
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = CreateDashboardDto::from($request->validated());
        $dto->user_id = (int) auth()->id();

        $dashboard = $this->handle($dto);

        return fractal($dashboard, new DashboardTransformer())
            ->withResourceName('dashboards')
            ->addMeta(['message' => 'Dashboard successfully created!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
