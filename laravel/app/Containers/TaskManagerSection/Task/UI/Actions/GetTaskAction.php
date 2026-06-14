<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\GetRequest;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;

class GetTaskAction extends BaseAction
{

    public function handle(Task $task): Task
    {
        return $task->load(['comments', 'reminder', 'checklists', 'progress']);
    }

    public function asController(Task $task, GetRequest $request): JsonResponse
    {
        $task = $this->handle($task);

        return fractal($task, new TaskTransformer())
            ->withResourceName('tasks')
            ->parseIncludes([
                'checklists.checklistItems',
                'progress',
                'reminder'
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
