<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\UI\Actions;

use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\TaskProgress\Data\DTO\TaskProgressCreateData;
use App\Containers\TaskManagerSection\TaskProgress\Models\TaskProgress;
use App\Containers\TaskManagerSection\TaskProgress\Tasks\CreateTaskProgressTask;
use App\Containers\TaskManagerSection\TaskProgress\UI\API\Requests\TaskProgressCreateRequest;
use App\Containers\TaskManagerSection\TaskProgress\UI\API\Transformers\TaskProgressTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class CreateTaskProgressAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK_PROGRESS;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly CreateTaskProgressTask $createTaskProgressTask
    )
    {
        parent::__construct();
    }

    public function handle(TaskProgressCreateData $dto): TaskProgress
    {
        $taskProgress = $this->createTaskProgressTask->run($dto);

        $this->recordUseCase($taskProgress);

        return $taskProgress;
    }

    public function asController(TaskProgressCreateRequest $request, Task $task): JsonResponse
    {
        $dto = TaskProgressCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;
        $dto->task_id = $task->id;

        $task = $this->handle($dto);

        return fractal($task, new TaskProgressTransformer())
            ->withResourceName('task_progress')
            ->addMeta(['message' => 'New Task Progress successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
