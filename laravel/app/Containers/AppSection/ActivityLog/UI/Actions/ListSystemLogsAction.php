<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\ListSystemLogsByUuid;
use App\Containers\AppSection\ActivityLog\UI\API\Requests\ListSystemLogsRequest;
use App\Containers\AppSection\ActivityLog\UI\API\Transformers\SystemLogTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListSystemLogsAction extends BaseAction
{
    public function __construct(
        private readonly ListSystemLogsByUuid $listSystemLogsByUuid,
    ) {
    }

    public function handle(string $correlationUuid): Collection
    {
        return $this->listSystemLogsByUuid->run($correlationUuid);
    }

    public function asController(ListSystemLogsRequest $request): JsonResponse
    {
        $logs = $this->handle($request->validated('correlation_uuid'));

        return fractal($logs, new SystemLogTransformer())
            ->withResourceName('system-logs')
            ->addMeta([
                'count' => $logs->count(),
                'correlation_uuid' => $request->validated('correlation_uuid'),
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
