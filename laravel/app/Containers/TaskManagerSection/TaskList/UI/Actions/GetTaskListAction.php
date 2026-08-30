<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\UI\Actions;

use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Containers\TaskManagerSection\TaskList\Tasks\GetTaskListTask;
use App\Containers\TaskManagerSection\TaskList\UI\API\Requests\GetRequest;
use App\Containers\TaskManagerSection\TaskList\UI\API\Transformers\TaskListTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetTaskListAction extends BaseAction
{
    private const TASK_RELATIONS = [
        'tasks.reminder',
        'tasks.comments.user',
        'tasks.checklists.checklistItems',
        'tasks.progress',
    ];

    public function __construct(
        private readonly GetTaskListTask $getTaskListTask
    ) {
    }

    /**
     * @param list<string> $with
     */
    public function handle(TaskList $taskList, array $with = []): TaskList
    {
        return $this->getTaskListTask->run($taskList, $with);
    }

    public function asController(TaskList $taskList, GetRequest $request): JsonResponse
    {
        $with = $this->shouldIncludeTasks($request) ? self::TASK_RELATIONS : [];
        $taskList = $this->handle($taskList, $with);

        return fractal($taskList, new TaskListTransformer())
            ->withResourceName('task-lists')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }

    private function shouldIncludeTasks(GetRequest $request): bool
    {
        $include = (string) $request->query('include', 'tasks');

        return str_contains($include, 'tasks');
    }
}
