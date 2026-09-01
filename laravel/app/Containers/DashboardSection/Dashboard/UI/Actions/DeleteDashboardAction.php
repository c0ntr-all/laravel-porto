<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Dashboard\Tasks\DeleteDashboardTask;
use App\Containers\DashboardSection\Dashboard\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteDashboardAction extends BaseAction
{
    public function __construct(
        private readonly DeleteDashboardTask $deleteDashboardTask,
    ) {
    }

    public function handle(Dashboard $dashboard): bool
    {
        return $this->deleteDashboardTask->run($dashboard);
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
