<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\Actions;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Containers\DashboardSection\Widget\Tasks\DeleteWidgetTask;
use App\Containers\DashboardSection\Widget\UI\API\Requests\DeleteRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeleteWidgetAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::DASHBOARD_WIDGET;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;

    public function __construct(
        private readonly DeleteWidgetTask $deleteWidgetTask,
    ) {
        parent::__construct();
    }

    public function handle(Widget $widget): bool
    {
        return DB::transaction(function () use ($widget): bool {
            $this->recordUseCase($widget);

            return $this->deleteWidgetTask->run($widget);
        });
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
