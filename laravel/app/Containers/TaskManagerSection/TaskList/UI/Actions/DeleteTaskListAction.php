<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\UI\Actions;

use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Containers\TaskManagerSection\TaskList\Tasks\DeleteTaskListTask;
use App\Containers\TaskManagerSection\TaskList\UI\API\Requests\DeleteRequest;
use Illuminate\Http\Response;
use App\Ship\Parents\Actions\BaseAction;

class DeleteTaskListAction extends BaseAction
{

    public function __construct(
        private readonly DeleteTaskListTask $deleteTaskListTask
    )
    {
    }

    public function handle(TaskList $taskList): bool
    {
        return $this->deleteTaskListTask->run($taskList);
    }

    public function asController(TaskList $taskList, DeleteRequest $request): Response
    {
        $this->handle($taskList);

        return response()->noContent();
    }
}
