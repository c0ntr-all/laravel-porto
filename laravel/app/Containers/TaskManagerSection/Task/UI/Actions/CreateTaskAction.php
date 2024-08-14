<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\TaskManagerSection\Task\Data\DTO\TaskCreateData;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\CreateTaskTask;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\CreateRequest;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTaskAction
{
    use AsAction;

    public function __construct(
        private readonly CreateTaskTask $createTaskTask
    )
    {
    }

    public function handle(TaskCreateData $dto): Task
    {
        return $this->createTaskTask->run($dto);
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = TaskCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $task = $this->handle($dto);

        return fractal($task, new TaskTransformer())
            ->withResourceName('tasks')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
