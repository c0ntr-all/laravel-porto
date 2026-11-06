<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\Actions;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistCreateData;
use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Containers\TaskManagerSection\Checklist\Tasks\CreateChecklistTask;
use App\Containers\TaskManagerSection\Checklist\UI\API\Requests\ChecklistCreateRequest;
use App\Containers\TaskManagerSection\Checklist\UI\API\Transformers\ChecklistTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class CreateChecklistAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_CHECKLIST;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly CreateChecklistTask $createChecklistTask
    )
    {
        parent::__construct();
    }

    public function handle(ChecklistCreateData $dto): Checklist
    {
        $checklist = $this->createChecklistTask->run($dto);

        $this->recordUseCase($checklist);

        return $checklist;
    }

    public function asController(ChecklistCreateRequest $request, Task $task): JsonResponse
    {
        $dto = ChecklistCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;
        $dto->task_id = $task->id;

        $task = $this->handle($dto);

        return fractal($task, new ChecklistTransformer())
            ->withResourceName('checklists')
            ->addMeta(['message' => 'New Checklist successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
