<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\AppSection\Attachment\Data\DTO\AttachmentsDeleteDto;
use App\Containers\AppSection\Attachment\Tasks\CreateAttachmentsTask;
use App\Containers\AppSection\Attachment\Tasks\DeleteAttachmentsTask;
use App\Containers\TaskManagerSection\Task\Data\DTO\TaskUpdateContextDto;
use App\Containers\TaskManagerSection\Task\Data\DTO\TaskUpdateData;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\UpdateTaskTask;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\UpdateRequest;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdateTaskAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly UpdateTaskTask $updateTaskTask,
        private readonly CreateAttachmentsTask $createAttachmentsTask,
        private readonly DeleteAttachmentsTask $deleteAttachmentsTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask
    ) {
        parent::__construct();
    }

    public function handle(Task $task, TaskUpdateContextDto $contextDto): Task
    {
        $updatedTask = DB::transaction(function () use ($task, $contextDto) {
            $taskUpdateDto = TaskUpdateData::from($contextDto->toArray());
            $updatedTask = $this->updateTaskTask->run($task, $taskUpdateDto);

            if (!empty($contextDto->attachments)) {
                $this->createAttachmentsTask->run(
                    $updatedTask,
                    $contextDto->user_id,
                    ContainerAliasEnum::TM_TASK->value,
                    $contextDto->attachments
                );
            }

            if (!empty($contextDto->deleted_attachments_ids)) {
                $this->deleteAttachmentsTask->run(
                    $updatedTask,
                    AttachmentsDeleteDto::from($contextDto->toArray())
                );
            }

            return $updatedTask;
        });

        DB::afterCommit(function () use ($updatedTask) {
            $this->createActivityUseCaseTask->run($updatedTask, $this->eventTypesEnum->value);
        });

        return $updatedTask;
    }

    public function asController(Task $task, UpdateRequest $request): JsonResponse
    {
        $contextDto = TaskUpdateContextDto::from([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        $task = $this->handle($task, $contextDto);

        return fractal($task, new TaskTransformer())
            ->parseIncludes(['attachments'])
            ->withResourceName('tasks')
            ->addMeta(['message' => 'Task successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
