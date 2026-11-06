<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\TaskManagerSection\Task\Data\DTO\ReorderTasksData;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\ReorderTasksTask;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\ReorderRequest;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ReorderTasksAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly ReorderTasksTask $reorderTasksTask,
    ) {
        parent::__construct();
    }

    public function handle(ReorderTasksData $dto): Collection
    {
        $priorities = Task::query()
            ->whereIn('id', $dto->ids)
            ->pluck('priority', 'id');

        $tasks = $this->reorderTasksTask->run($dto);

        foreach ($tasks as $task) {
            if ((string) $priorities->get($task->id) !== (string) $task->priority) {
                $this->recordUseCase($task);
            }
        }

        return $tasks;
    }

    public function asController(ReorderRequest $request): JsonResponse
    {
        $tasks = $this->handle(ReorderTasksData::from($request->validated()));

        return fractal($tasks, new TaskTransformer())
            ->withResourceName('tasks')
            ->addMeta([
                'count' => $tasks->count(),
                'message' => 'Tasks successfully reordered!',
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
