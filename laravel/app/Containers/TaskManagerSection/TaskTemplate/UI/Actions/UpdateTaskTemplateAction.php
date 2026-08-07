<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\UI\Actions;

use App\Containers\TaskManagerSection\TaskTemplate\Data\DTO\TaskTemplateUpdateData;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Containers\TaskManagerSection\TaskTemplate\Tasks\UpdateTaskTemplateTask;
use App\Containers\TaskManagerSection\TaskTemplate\UI\API\Requests\UpdateRequest;
use App\Containers\TaskManagerSection\TaskTemplate\UI\API\Transformers\TaskTemplateTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdateTaskTemplateAction extends BaseAction
{
    public function __construct(
        private readonly UpdateTaskTemplateTask $updateTaskTemplateTask
    ) {
    }

    public function handle(TaskTemplate $taskTemplate, TaskTemplateUpdateData $dto): TaskTemplate
    {
        return DB::transaction(fn () => $this->updateTaskTemplateTask->run($taskTemplate, $dto));
    }

    public function asController(TaskTemplate $taskTemplate, UpdateRequest $request): JsonResponse
    {
        $dto = TaskTemplateUpdateData::from($request->validated());

        $taskTemplate = $this->handle($taskTemplate, $dto);

        return fractal($taskTemplate, new TaskTemplateTransformer())
            ->withResourceName('task-templates')
            ->parseIncludes(['checklists'])
            ->addMeta(['message' => 'Task template successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
