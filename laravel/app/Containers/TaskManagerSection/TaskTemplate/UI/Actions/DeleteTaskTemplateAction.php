<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\UI\Actions;

use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Containers\TaskManagerSection\TaskTemplate\Tasks\DeleteTaskTemplateTask;
use App\Containers\TaskManagerSection\TaskTemplate\UI\API\Requests\DeleteRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeleteTaskTemplateAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK_TEMPLATE;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;

    public function __construct(
        private readonly DeleteTaskTemplateTask $deleteTaskTemplateTask
    ) {
        parent::__construct();
    }

    public function handle(TaskTemplate $taskTemplate): ?bool
    {
        return DB::transaction(function () use ($taskTemplate): ?bool {
            $this->recordUseCase($taskTemplate);

            return $this->deleteTaskTemplateTask->run($taskTemplate);
        });
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
