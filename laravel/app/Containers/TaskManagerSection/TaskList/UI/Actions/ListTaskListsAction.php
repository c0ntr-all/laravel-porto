<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\UI\Actions;

use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Containers\TaskManagerSection\TaskList\Tasks\ListTaskListsTask;
use App\Containers\TaskManagerSection\TaskList\UI\API\Requests\ListRequest;
use App\Containers\TaskManagerSection\TaskList\UI\API\Transformers\TaskListTransformer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class ListTaskListsAction
{
    use AsAction;

    public function __construct(
        private readonly ListTaskListsTask $listTaskListsTask
    )
    {
    }

    public function handle(): Collection
    {
        return $this->listTaskListsTask->run();
    }

    public function asController(TaskList $taskList, ListRequest $request): JsonResponse
    {
        $taskLists = $this->handle();

        return fractal($taskLists, new TaskListTransformer())
            ->withResourceName('task-lists')
            ->parseIncludes(['tasks'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
