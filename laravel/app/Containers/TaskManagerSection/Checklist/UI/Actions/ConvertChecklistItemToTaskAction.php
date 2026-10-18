<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\Actions;

use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Containers\TaskManagerSection\Checklist\Tasks\ConvertChecklistItemToTaskTask;
use App\Containers\TaskManagerSection\Checklist\UI\API\Requests\ConvertChecklistItemToTaskRequest;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConvertChecklistItemToTaskAction extends BaseAction
{
    public function __construct(
        private readonly ConvertChecklistItemToTaskTask $convertChecklistItemToTaskTask,
    ) {
    }

    public function handle(ChecklistItem $checklistItem, int $userId): Task
    {
        return $this->convertChecklistItemToTaskTask->run($checklistItem, $userId);
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
