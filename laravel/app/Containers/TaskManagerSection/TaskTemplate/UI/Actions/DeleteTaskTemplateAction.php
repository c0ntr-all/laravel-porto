<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\UI\Actions;

use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Containers\TaskManagerSection\TaskTemplate\Tasks\DeleteTaskTemplateTask;
use App\Containers\TaskManagerSection\TaskTemplate\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteTaskTemplateAction extends BaseAction
{
    public function __construct(
        private readonly DeleteTaskTemplateTask $deleteTaskTemplateTask
    ) {
    }

    public function handle(TaskTemplate $taskTemplate): ?bool
    {
        return $this->deleteTaskTemplateTask->run($taskTemplate);
    }

    public function asController(TaskTemplate $taskTemplate, DeleteRequest $request): JsonResponse
    {
        $this->handle($taskTemplate);

        return response()->json([
            'meta' => [
                'message' => 'Task template successfully deleted!',
            ],
        ]);
    }
}
