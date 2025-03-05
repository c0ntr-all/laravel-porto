<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\Actions;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistItemUpdateData;
use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Containers\TaskManagerSection\Checklist\Tasks\UpdateChecklistItemTask;
use App\Containers\TaskManagerSection\Checklist\UI\API\Requests\ChecklistItemUpdateRequest;
use App\Containers\TaskManagerSection\Checklist\UI\API\Transformers\ChecklistItemTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateChecklistItemAction
{
    use AsAction;

    public function __construct(
        private readonly UpdateChecklistItemTask $updateChecklistItemTask
    )
    {
    }

    public function handle(ChecklistItem $checklistItem, ChecklistItemUpdateData $dto): ChecklistItem
    {
        return $this->updateChecklistItemTask->run($checklistItem, $dto);
    }

    public function asController(Task $task, Checklist $checklist, ChecklistItem $checklistItem, ChecklistItemUpdateRequest $request): JsonResponse
    {
        $dto = ChecklistItemUpdateData::from($request->validated());
        if (isset($request->is_finished)) {
            $dto->finished_at = $request->is_finished === true ? Carbon::now() : null;
        }

        $checklistItem = $this->handle($checklistItem, $dto);

        return fractal($checklistItem, new ChecklistItemTransformer())
            ->withResourceName('checklist_items')
            ->addMeta(['message' => 'Checklist item successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
