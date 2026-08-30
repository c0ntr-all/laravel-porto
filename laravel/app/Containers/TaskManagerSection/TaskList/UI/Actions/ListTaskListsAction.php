<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\UI\Actions;

use App\Containers\TaskManagerSection\TaskList\Tasks\ListTaskListsTask;
use App\Containers\TaskManagerSection\TaskList\UI\API\Requests\ListRequest;
use App\Containers\TaskManagerSection\TaskList\UI\API\Transformers\TaskListTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListTaskListsAction extends BaseAction
{
    private const TASK_RELATIONS = [
        'tasks.reminder',
        'tasks.comments.user',
        'tasks.checklists.checklistItems',
        'tasks.progress',
    ];

    public function __construct(
        private readonly ListTaskListsTask $listTaskListsTask
    ) {
    }

    /**
     * @param list<string> $with
     */
    public function handle(array $with = []): Collection
    {
        return $this->listTaskListsTask->run($with);
    }

    public function asController(ListRequest $request): JsonResponse
    {
        $with = $this->shouldIncludeTasks($request) ? self::TASK_RELATIONS : [];
        $taskLists = $this->handle($with);

        return fractal($taskLists, new TaskListTransformer())
            ->withResourceName('task-lists')
            ->addMeta(['count' => $taskLists->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }

    private function shouldIncludeTasks(ListRequest $request): bool
    {
        $include = (string) $request->query('include', '');

        return $include !== '' && str_contains($include, 'tasks');
    }
}
