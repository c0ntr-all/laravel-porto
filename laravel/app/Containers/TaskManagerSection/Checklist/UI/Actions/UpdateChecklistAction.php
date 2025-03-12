<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\Actions;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistUpdateData;
use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Containers\TaskManagerSection\Checklist\Tasks\UpdateChecklistTask;
use App\Containers\TaskManagerSection\Checklist\UI\API\Requests\ChecklistUpdateRequest;
use App\Containers\TaskManagerSection\Checklist\UI\API\Transformers\ChecklistTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateChecklistAction
{
    use AsAction;

    public function __construct(
        private readonly UpdateChecklistTask $updateChecklistTask
    )
    {
    }

    public function handle(Checklist $checklist, ChecklistUpdateData $dto): Checklist
    {
        return $this->updateChecklistTask->run($checklist, $dto);
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
