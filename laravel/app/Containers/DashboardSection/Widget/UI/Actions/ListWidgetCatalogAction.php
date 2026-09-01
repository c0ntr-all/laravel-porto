<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\Actions;

use App\Containers\DashboardSection\Widget\Tasks\ListWidgetCatalogTask;
use App\Containers\DashboardSection\Widget\UI\API\Requests\CatalogRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ListWidgetCatalogAction extends BaseAction
{
    public function __construct(
        private readonly ListWidgetCatalogTask $listWidgetCatalogTask,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(): array
    {
        return $this->listWidgetCatalogTask->run();
    }

    public function asController(CatalogRequest $request): JsonResponse
    {
        $catalog = $this->handle();

        return response()->json([
            'data' => $catalog,
            'meta' => [
                'count' => count($catalog),
            ],
        ]);
    }
}
