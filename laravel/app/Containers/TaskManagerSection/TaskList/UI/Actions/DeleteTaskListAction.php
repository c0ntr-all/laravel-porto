<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\UI\Actions;

use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Containers\TaskManagerSection\TaskList\Tasks\DeleteTaskListTask;
use App\Containers\TaskManagerSection\TaskList\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteTaskListAction extends BaseAction
{
    public function __construct(
        private readonly DeleteTaskListTask $deleteTaskListTask
    ) {
    }

    public function handle(TaskList $taskList): bool
    {
        return $this->deleteTaskListTask->run($taskList);
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
