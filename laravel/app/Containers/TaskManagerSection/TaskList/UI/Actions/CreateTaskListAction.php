<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\UI\Actions;

use App\Containers\TaskManagerSection\TaskList\Data\DTO\TaskListCreateData;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Containers\TaskManagerSection\TaskList\Tasks\CreateTaskListTask;
use App\Containers\TaskManagerSection\TaskList\UI\API\Requests\UpdateRequest;
use App\Containers\TaskManagerSection\TaskList\UI\API\Transformers\TaskListTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTaskListAction
{
    use AsAction;

    public function __construct(
        private readonly CreateTaskListTask $createTaskListTask
    )
    {
    }

    public function handle(TaskListCreateData $dto): TaskList
    {
        return $this->createTaskListTask->run($dto);
    }

    public function asController(UpdateRequest $request): JsonResponse
    {
        $dto = TaskListCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $taskList = $this->handle($dto);

        return fractal($taskList, new TaskListTransformer())
            ->withResourceName('task-lists')
            ->addMeta(['message' => 'New task list successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
