<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Tasks;

use App\Containers\TaskManagerSection\Task\Data\DTO\ReorderTasksData;
use App\Containers\TaskManagerSection\Task\Data\Repositories\TaskRepository;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ReorderTasksTask extends ParentTask
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
    ) {
    }

    /**
     * @return Collection<int, Task>
     */
    public function run(ReorderTasksData $dto): Collection
    {
        $tasks = Task::query()->whereIn('id', $dto->ids)->get();

        if ($tasks->count() !== count($dto->ids)) {
            throw new UnprocessableEntityHttpException('One or more tasks were not found.');
        }

        $this->assertSameSiblingScope($tasks);

        DB::transaction(function () use ($dto): void {
            $this->taskRepository->reorder($dto->ids);
        });

        return $this->taskRepository->findManyOrderedByIds($dto->ids);
    }

    /**
     * @param Collection<int, Task> $tasks
     */
    private function assertSameSiblingScope(Collection $tasks): void
    {
        $first = $tasks->first();
        if ($first === null) {
            return;
        }

        $parentId = $first->parent_id;
        $taskListId = $first->task_list_id;

        $mismatched = $tasks->contains(static function (Task $task) use ($parentId, $taskListId): bool {
            if ($task->parent_id !== $parentId) {
                return true;
            }

            if ($parentId !== null) {
                return false;
            }

            return $task->task_list_id !== $taskListId;
        });

        if ($mismatched) {
            throw new UnprocessableEntityHttpException(
                'All tasks must belong to the same list and parent scope.'
            );
        }
    }
}
