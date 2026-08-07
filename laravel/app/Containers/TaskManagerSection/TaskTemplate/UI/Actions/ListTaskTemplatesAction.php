<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\UI\Actions;

use App\Containers\TaskManagerSection\TaskTemplate\Tasks\ListTaskTemplatesTask;
use App\Containers\TaskManagerSection\TaskTemplate\UI\API\Requests\ListRequest;
use App\Containers\TaskManagerSection\TaskTemplate\UI\API\Transformers\TaskTemplateTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListTaskTemplatesAction extends BaseAction
{
    public function __construct(
        private readonly ListTaskTemplatesTask $listTaskTemplatesTask
    ) {
    }

    public function handle(): Collection
    {
        return $this->listTaskTemplatesTask->run();
    }

    public function asController(ListRequest $request): JsonResponse
    {
        $taskTemplates = $this->handle();

        return fractal($taskTemplates, new TaskTemplateTransformer())
            ->withResourceName('task-templates')
            ->parseIncludes(['checklists'])
            ->addMeta(['count' => $taskTemplates->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
