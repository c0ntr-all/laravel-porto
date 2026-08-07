<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\UI\Actions;

use App\Containers\TaskManagerSection\TaskTemplate\Data\DTO\TaskTemplateCreateData;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Containers\TaskManagerSection\TaskTemplate\Tasks\CreateTaskTemplateTask;
use App\Containers\TaskManagerSection\TaskTemplate\UI\API\Requests\CreateRequest;
use App\Containers\TaskManagerSection\TaskTemplate\UI\API\Transformers\TaskTemplateTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreateTaskTemplateAction extends BaseAction
{
    public function __construct(
        private readonly CreateTaskTemplateTask $createTaskTemplateTask
    ) {
    }

    public function handle(TaskTemplateCreateData $dto): TaskTemplate
    {
        return DB::transaction(fn () => $this->createTaskTemplateTask->run($dto));
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = TaskTemplateCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $taskTemplate = $this->handle($dto);

        return fractal($taskTemplate, new TaskTemplateTransformer())
            ->withResourceName('task-templates')
            ->parseIncludes(['checklists'])
            ->addMeta(['message' => 'New task template successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
