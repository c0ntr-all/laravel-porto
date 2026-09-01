<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Containers\DashboardSection\Widget\Tasks\DeleteWidgetTask;
use App\Containers\DashboardSection\Widget\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteWidgetAction extends BaseAction
{
    public function __construct(
        private readonly DeleteWidgetTask $deleteWidgetTask,
    ) {
    }

    public function handle(Widget $widget): bool
    {
        return $this->deleteWidgetTask->run($widget);
    }

    public function asController(Dashboard $dashboard, Widget $widget, DeleteRequest $request): JsonResponse
    {
        abort_unless($widget->dashboard_id === $dashboard->id, 404);

        $this->handle($widget);

        return response()->json([
            'meta' => [
                'message' => 'Widget successfully deleted!',
            ],
        ]);
    }
}
