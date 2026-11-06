<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Dashboard\Tasks\DeleteDashboardTask;
use App\Containers\DashboardSection\Dashboard\UI\API\Requests\DeleteRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeleteDashboardAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::DASHBOARD;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;

    public function __construct(
        private readonly DeleteDashboardTask $deleteDashboardTask,
    ) {
        parent::__construct();
    }

    public function handle(Dashboard $dashboard): bool
    {
        return DB::transaction(function () use ($dashboard): bool {
            $this->recordUseCase($dashboard);

            return $this->deleteDashboardTask->run($dashboard);
        });
    }

    public function asController(Dashboard $dashboard, DeleteRequest $request): JsonResponse
    {
        $this->handle($dashboard);

        return response()->json([
            'meta' => [
                'message' => 'Dashboard successfully deleted!',
            ],
        ]);
    }
}
