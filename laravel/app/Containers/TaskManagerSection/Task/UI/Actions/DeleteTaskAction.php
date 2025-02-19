<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\DeleteTaskTask;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\DeleteRequest;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteTaskAction
{
    use AsAction;

    public function __construct(
        private readonly DeleteTaskTask $deleteTaskTask
    )
    {
    }

    public function handle(Task $task): bool
    {
        return $this->deleteTaskTask->run($task);
    }

    public function asController(Task $task, DeleteRequest $request): JsonResponse
    {
        $this->handle($task);

        return response()->json(
            ['meta' => [
                'message' => 'Task successfully deleted!'
            ]]
        );
    }
}
