<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\TaskManagerSection\Task\Data\DTO\ReorderTasksData;
use App\Containers\TaskManagerSection\Task\Tasks\ReorderTasksTask;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\ReorderRequest;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ReorderTasksAction extends BaseAction
{
    public function __construct(
        private readonly ReorderTasksTask $reorderTasksTask,
    ) {
    }

    public function handle(ReorderTasksData $dto): Collection
    {
        return $this->reorderTasksTask->run($dto);
    }

    public function asController(ReorderRequest $request): JsonResponse
    {
        $tasks = $this->handle(ReorderTasksData::from($request->validated()));

        return fractal($tasks, new TaskTransformer())
            ->withResourceName('tasks')
            ->addMeta([
                'count' => $tasks->count(),
                'message' => 'Tasks successfully reordered!',
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
