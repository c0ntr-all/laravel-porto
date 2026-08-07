<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\UI\Actions;

use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Containers\TaskManagerSection\TaskTemplate\Tasks\GetTaskTemplateTask;
use App\Containers\TaskManagerSection\TaskTemplate\UI\API\Requests\GetRequest;
use App\Containers\TaskManagerSection\TaskTemplate\UI\API\Transformers\TaskTemplateTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetTaskTemplateAction extends BaseAction
{
    public function __construct(
        private readonly GetTaskTemplateTask $getTaskTemplateTask
    ) {
    }

    public function handle(TaskTemplate $taskTemplate): TaskTemplate
    {
        return $this->getTaskTemplateTask->run($taskTemplate);
    }

    public function asController(TaskTemplate $taskTemplate, GetRequest $request): JsonResponse
    {
        $taskTemplate = $this->handle($taskTemplate);

        return fractal($taskTemplate, new TaskTemplateTransformer())
            ->withResourceName('task-templates')
            ->parseIncludes(['checklists'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
