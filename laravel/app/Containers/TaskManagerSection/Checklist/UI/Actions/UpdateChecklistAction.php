<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\Actions;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistUpdateData;
use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Containers\TaskManagerSection\Checklist\Tasks\UpdateChecklistTask;
use App\Containers\TaskManagerSection\Checklist\UI\API\Requests\ChecklistUpdateRequest;
use App\Containers\TaskManagerSection\Checklist\UI\API\Transformers\ChecklistTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class UpdateChecklistAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_CHECKLIST;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly UpdateChecklistTask $updateChecklistTask
    )
    {
        parent::__construct();
    }

    public function handle(Checklist $checklist, ChecklistUpdateData $dto): Checklist
    {
        $checklist = $this->updateChecklistTask->run($checklist, $dto);

        $this->recordUseCase($checklist);

        return $checklist;
    }

    public function asController(Task $task, Checklist $checklist, ChecklistUpdateRequest $request): JsonResponse
    {
        $dto = ChecklistUpdateData::from($request->validated());

        $checklist = $this->handle($checklist, $dto);

        return fractal($checklist, new ChecklistTransformer())
            ->withResourceName('checklists')
            ->addMeta(['message' => 'Checklist successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
