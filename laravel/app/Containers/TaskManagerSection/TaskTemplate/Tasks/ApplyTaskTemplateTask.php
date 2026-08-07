<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\Tasks;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistCreateData;
use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistItemCreateData;
use App\Containers\TaskManagerSection\Checklist\Tasks\CreateChecklistItemTask;
use App\Containers\TaskManagerSection\Checklist\Tasks\CreateChecklistTask;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Ship\Parents\Tasks\Task as ParentTask;

class ApplyTaskTemplateTask extends ParentTask
{
    public function __construct(
        private readonly CreateChecklistTask $createChecklistTask,
        private readonly CreateChecklistItemTask $createChecklistItemTask
    ) {
    }

    public function run(Task $task, TaskTemplate $template): Task
    {
        $template->loadMissing(['checklists.items']);

        foreach ($template->checklists as $templateChecklist) {
            $checklistDto = ChecklistCreateData::from([
                'user_id' => $task->user_id,
                'task_id' => $task->id,
                'title' => $templateChecklist->title,
            ]);

            $checklist = $this->createChecklistTask->run($checklistDto);

            foreach ($templateChecklist->items as $templateItem) {
                $itemDto = ChecklistItemCreateData::from([
                    'user_id' => $task->user_id,
                    'checklist_id' => $checklist->id,
                    'title' => $templateItem->title,
                    'position' => $templateItem->position,
                ]);

                $this->createChecklistItemTask->run($itemDto);
            }
        }

        return $task->load(['checklists.checklistItems']);
    }
}
