<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\Actions;

use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Containers\TaskManagerSection\Checklist\Tasks\DeleteChecklistItemTask;
use App\Containers\TaskManagerSection\Checklist\UI\API\Requests\ChecklistItemDeleteRequest;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteChecklistItemAction
{
    use AsAction;

    public function __construct(
        private readonly DeleteChecklistItemTask $deleteChecklistItemTask
    )
    {
    }

    public function handle(ChecklistItem $checklistItem): bool
    {
        return $this->deleteChecklistItemTask->run($checklistItem);
    }

    public function asController(Task $task, Checklist $checklist, ChecklistItem $checklistItem, ChecklistItemDeleteRequest $request): JsonResponse
    {
        $this->handle($checklistItem);

        return response()->json(
            ['meta' => [
                'message' => 'Checklist item successfully deleted!'
            ]]
        );
    }
}
