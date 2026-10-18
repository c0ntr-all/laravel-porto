<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Tasks;

use App\Containers\TaskManagerSection\Checklist\Data\Repositories\ChecklistItemRepository;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Containers\TaskManagerSection\Task\Data\DTO\TaskCreateData;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\CreateTaskTask;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ConvertChecklistItemToTaskTask extends ParentTask
{
    public function __construct(
        private readonly CreateTaskTask $createTaskTask,
        private readonly ChecklistItemRepository $checklistItemRepository,
    ) {
    }

    public function run(ChecklistItem $checklistItem, int $userId): Task
    {
        if ($checklistItem->converted_task_id !== null) {
            throw new UnprocessableEntityHttpException('Checklist item has already been converted to a task.');
        }

        return DB::transaction(function () use ($checklistItem, $userId) {
            $checklistItem->loadMissing('checklist.task');

            $parentTask = $checklistItem->checklist->task;

            $task = $this->createTaskTask->run(TaskCreateData::from([
                'user_id' => $userId,
                'parent_id' => $parentTask->id,
                'task_list_id' => $parentTask->task_list_id,
                'title' => mb_substr($checklistItem->title, 0, 70),
            ]));

            $this->checklistItemRepository->update($checklistItem, [
                'converted_task_id' => $task->id,
            ]);

            return $task;
        });
    }
}
