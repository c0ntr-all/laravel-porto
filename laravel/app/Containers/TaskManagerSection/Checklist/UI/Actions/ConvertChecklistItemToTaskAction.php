<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\Actions;

use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Containers\TaskManagerSection\Checklist\Tasks\ConvertChecklistItemToTaskTask;
use App\Containers\TaskManagerSection\Checklist\UI\API\Requests\ConvertChecklistItemToTaskRequest;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConvertChecklistItemToTaskAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly ConvertChecklistItemToTaskTask $convertChecklistItemToTaskTask,
    ) {
        parent::__construct();
    }

    public function handle(ChecklistItem $checklistItem, int $userId): Task
    {
        $task = $this->convertChecklistItemToTaskTask->run($checklistItem, $userId);

        $this->recordUseCase($task);

        return $task;
    }

    public function asController(
        Task $task,
        Checklist $checklist,
        ChecklistItem $checklistItem,
        ConvertChecklistItemToTaskRequest $request,
    ): JsonResponse {
        if ((int) $checklist->task_id !== (int) $task->id
            || (int) $checklistItem->checklist_id !== (int) $checklist->id
        ) {
            throw new NotFoundHttpException('Checklist item not found for this task.');
        }

        $createdTask = $this->handle($checklistItem, (int) auth()->id());

        return fractal($createdTask, new TaskTransformer())
            ->parseIncludes(['parent', 'sourceChecklistItem'])
            ->withResourceName('tasks')
            ->addMeta(['message' => 'Checklist item successfully converted to task!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
