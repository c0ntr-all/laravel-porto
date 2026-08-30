<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\TaskManagerSection\Task\Tasks\ListTasksTask;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\ListRequest;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListTasksAction extends BaseAction
{
    public function __construct(
        private readonly ListTasksTask $listTasksTask
    ) {
    }

    /**
     * @param array{task_list_id?: int|null, unlisted?: bool} $filters
     * @param list<string> $with
     */
    public function handle(array $filters = [], array $with = []): Collection
    {
        return $this->listTasksTask->run($filters, $with);
    }

    public function asController(ListRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $filters = [];

        if (array_key_exists('task_list_id', $validated)) {
            $filters['task_list_id'] = $validated['task_list_id'];
        }

        if (($validated['unlisted'] ?? false) === true || ($validated['unlisted'] ?? null) === '1') {
            $filters['unlisted'] = true;
        }

        $with = $this->relationsFromInclude((string) $request->query('include', ''));
        $tasks = $this->handle($filters, $with);

        return fractal($tasks, new TaskTransformer())
            ->withResourceName('tasks')
            ->addMeta(['count' => $tasks->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }

    /**
     * @return list<string>
     */
    private function relationsFromInclude(string $include): array
    {
        $with = [];

        if (str_contains($include, 'reminder')) {
            $with[] = 'reminder';
        }
        if (str_contains($include, 'checklists')) {
            $with[] = 'checklists.checklistItems';
        }
        if (str_contains($include, 'progress')) {
            $with[] = 'progress';
        }
        if (str_contains($include, 'comments')) {
            $with[] = 'comments.user';
        }

        return $with;
    }
}
