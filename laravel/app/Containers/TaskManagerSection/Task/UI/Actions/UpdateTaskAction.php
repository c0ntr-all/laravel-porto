<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\TaskManagerSection\Task\Data\DTO\TaskUpdateData;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\UpdateTaskTask;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\UpdateRequest;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTaskAction
{
    use AsAction;

    public function __construct(
        private readonly UpdateTaskTask $updateTaskTask
    )
    {
    }

    public function handle(Task $task, TaskUpdateData $dto): Task
    {
        return $this->updateTaskTask->run($task, $dto);
    }

    public function asController(Task $task, UpdateRequest $request): JsonResponse
    {
        $dto = TaskUpdateData::from($request->validated());

        $task = $this->handle($task, $dto);

        return fractal($task, new TaskTransformer())
            ->withResourceName('tasks')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}