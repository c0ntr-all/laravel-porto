<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Data\DTO\UpdateDashboardDto;
use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Dashboard\Tasks\UpdateDashboardTask;
use App\Containers\DashboardSection\Dashboard\UI\API\Requests\UpdateRequest;
use App\Containers\DashboardSection\Dashboard\UI\API\Transformers\DashboardTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class UpdateDashboardAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::DASHBOARD;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly UpdateDashboardTask $updateDashboardTask,
    ) {
        parent::__construct();
    }

    public function handle(Dashboard $dashboard, UpdateDashboardDto $dto): Dashboard
    {
        $dashboard = $this->updateDashboardTask->run($dashboard, $dto)->load('widgets');

        $this->recordUseCase($dashboard);

        return $dashboard;
    }

    public function asController(Dashboard $dashboard, UpdateRequest $request): JsonResponse
    {
        $dto = UpdateDashboardDto::from($request->validated());
        $dashboard = $this->handle($dashboard, $dto);

        return fractal($dashboard, new DashboardTransformer())
            ->withResourceName('dashboards')
            ->addMeta(['message' => 'Dashboard successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
