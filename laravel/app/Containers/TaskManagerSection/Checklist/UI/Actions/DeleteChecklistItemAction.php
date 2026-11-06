<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\Actions;

use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Containers\TaskManagerSection\Checklist\Tasks\DeleteChecklistItemTask;
use App\Containers\TaskManagerSection\Checklist\UI\API\Requests\ChecklistItemDeleteRequest;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeleteChecklistItemAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_CHECKLIST_ITEM;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;

    public function __construct(
        private readonly DeleteChecklistItemTask $deleteChecklistItemTask
    )
    {
        parent::__construct();
    }

    public function handle(ChecklistItem $checklistItem): bool
    {
        return DB::transaction(function () use ($checklistItem): bool {
            $this->recordUseCase($checklistItem);

            return $this->deleteChecklistItemTask->run($checklistItem);
        });
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
