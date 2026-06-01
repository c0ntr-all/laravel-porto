<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\TaskManagerSection\Task\Data\DTO\TaskUpdateData;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\UpdateTaskTask;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\UpdateRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateTaskAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly UpdateTaskTask $updateTaskTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask
    )
    {
        parent::__construct();
    }

    public function handle(Task $task, TaskUpdateData $dto): Task
    {
        $updatedTask = $this->updateTaskTask->run($task, $dto);

        // После успешного коммита формируем user_log
        DB::afterCommit(function () use ($updatedTask) {
            $this->createActivityUseCaseTask->run($updatedTask, $this->eventTypesEnum->value);
        });

        return $updatedTask;
    }

    public function asController(Task $task, UpdateRequest $request): JsonResponse
    {
        $dto = TaskUpdateData::from($request->validated());
        if (isset($request->is_finished)) {
            $dto->finished_at = $request->is_finished === true ? Carbon::now() : null;
        }

        $task = $this->handle($task, $dto);

        return fractal($task, new TaskTransformer())
            ->withResourceName('tasks')
            ->addMeta(['message' => 'Task successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
