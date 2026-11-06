<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\UI\Actions;

use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Containers\TaskManagerSection\TaskList\Tasks\DeleteTaskListTask;
use App\Containers\TaskManagerSection\TaskList\UI\API\Requests\DeleteRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeleteTaskListAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK_LIST;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;

    public function __construct(
        private readonly DeleteTaskListTask $deleteTaskListTask
    ) {
        parent::__construct();
    }

    public function handle(TaskList $taskList): bool
    {
        return DB::transaction(function () use ($taskList): bool {
            $this->recordUseCase($taskList);

            return $this->deleteTaskListTask->run($taskList);
        });
    }

    public function asController(TaskList $taskList, DeleteRequest $request): JsonResponse
    {
        $this->handle($taskList);

        return response()->json([
            'meta' => [
                'message' => 'Task list successfully deleted!',
            ],
        ]);
    }
}
