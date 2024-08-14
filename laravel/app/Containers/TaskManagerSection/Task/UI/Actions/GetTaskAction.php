<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\GetRequest;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTaskAction
{
    use AsAction;

    public function handle(Task $task): Task
    {
        return $task->load('comments');
    }

    public function asController(Task $task, GetRequest $request): JsonResponse
    {
        $task = $this->handle($task);

        return fractal($task, new TaskTransformer())
            ->withResourceName('tasks')
            ->parseIncludes(['comments'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
