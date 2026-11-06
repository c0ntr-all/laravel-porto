<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\UI\Actions;

use App\Containers\TaskManagerSection\TaskProgress\Data\DTO\TaskProgressUpdateData;
use App\Containers\TaskManagerSection\TaskProgress\Models\TaskProgress;
use App\Containers\TaskManagerSection\TaskProgress\Tasks\UpdateTaskProgressTask;
use App\Containers\TaskManagerSection\TaskProgress\UI\API\Requests\TaskProgressUpdateRequest;
use App\Containers\TaskManagerSection\TaskProgress\UI\API\Transformers\TaskProgressTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class UpdateTaskProgressAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK_PROGRESS;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly UpdateTaskProgressTask $updateTaskProgressTask
    )
    {
        parent::__construct();
    }

    public function handle(TaskProgress $taskProgress, TaskProgressUpdateData $dto): TaskProgress
    {
        $taskProgress = $this->updateTaskProgressTask->run($taskProgress, $dto);

        $this->recordUseCase($taskProgress);

        return $taskProgress;
    }

    public function asController(Task $task, TaskProgress $progress, TaskProgressUpdateRequest $request): JsonResponse
    {
        $dto = TaskProgressUpdateData::from($request->validated());

        $taskProgress = $this->handle($progress, $dto);

        return fractal($taskProgress, new TaskProgressTransformer())
            ->withResourceName('task_progress')
            ->addMeta(['message' => 'Task Progress successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
