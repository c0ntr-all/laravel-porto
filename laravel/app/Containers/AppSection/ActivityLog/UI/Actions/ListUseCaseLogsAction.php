<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\ListUseCaseLogsTask;
use App\Containers\AppSection\ActivityLog\UI\API\Requests\ListRequest;
use App\Containers\AppSection\ActivityLog\UI\API\Transformers\UseCaseLogTransformer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class ListUseCaseLogsAction
{
    use AsAction;

    public function __construct(
        private readonly ListUseCaseLogsTask $listUseCaseLogsTask
    )
    {
    }

    public function handle(): Collection
    {
        return $this->listUseCaseLogsTask->run();
    }

    public function asController(ListRequest $request): JsonResponse
    {
        $logs = $this->handle();

        return fractal($logs, new UseCaseLogTransformer())
            ->withResourceName('use-case-logs')
            ->parseIncludes([
                'user',
            ])
            ->addMeta([
                'count' => $logs->count()
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
