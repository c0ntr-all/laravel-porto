<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\Actions;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistItemCreateData;
use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Containers\TaskManagerSection\Checklist\Tasks\CreateChecklistItemTask;
use App\Containers\TaskManagerSection\Checklist\UI\API\Requests\ChecklistItemCreateRequest;
use App\Containers\TaskManagerSection\Checklist\UI\API\Transformers\ChecklistItemTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class CreateChecklistItemAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_CHECKLIST_ITEM;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly CreateChecklistItemTask $createChecklistItemTask
    )
    {
        parent::__construct();
    }

    public function handle(ChecklistItemCreateData $dto): ChecklistItem
    {
        $checklistItem = $this->createChecklistItemTask->run($dto);

        $this->recordUseCase($checklistItem);

        return $checklistItem;
    }

    public function asController(ChecklistItemCreateRequest $request, Task $task,Checklist $checklist): JsonResponse
    {
        $dto = ChecklistItemCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;
        $dto->checklist_id = $checklist->id;

        $task = $this->handle($dto);

        return fractal($task, new ChecklistItemTransformer())
            ->withResourceName('checklist_items')
            ->addMeta(['message' => 'New Checklist item successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
