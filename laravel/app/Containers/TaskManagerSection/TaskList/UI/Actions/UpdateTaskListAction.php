<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\UI\Actions;

use App\Containers\TaskManagerSection\TaskList\Data\DTO\TaskListUpdateData;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Containers\TaskManagerSection\TaskList\Tasks\UpdateTaskListTask;
use App\Containers\TaskManagerSection\TaskList\UI\API\Requests\UpdateRequest;
use App\Containers\TaskManagerSection\TaskList\UI\API\Transformers\TaskListTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTaskListAction
{
    use AsAction;

    public function __construct(
        private readonly UpdateTaskListTask $updateTaskListTask
    )
    {
    }

    public function handle(TaskList $taskList, TaskListUpdateData $dto): TaskList
    {
        return $this->updateTaskListTask->run($taskList, $dto);
    }

    public function asController(TaskList $taskList, UpdateRequest $request): JsonResponse
    {
        $dto = TaskListUpdateData::from($request->validated());

        $taskList = $this->handle($taskList, $dto);

        return fractal($taskList, new TaskListTransformer())
            ->withResourceName('task-lists')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
