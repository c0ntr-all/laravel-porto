<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Data\Repositories;

use App\Containers\TaskManagerSection\Task\Data\DTO\TaskCreateData;
use App\Containers\TaskManagerSection\Task\Data\DTO\TaskUpdateData;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;

class TaskRepository
{
    /**
     * @param array{
     *     task_list_id?: int|null,
     *     unlisted?: bool,
     *     parent_id?: int|null,
     *     roots_only?: bool,
     *     is_favorite?: bool
     * } $filters
     * @param list<string> $with
     */
    public function list(array $filters = [], array $with = []): Collection
    {
        $query = Task::query()
            ->orderBy('priority')
            ->orderBy('id');

        if (array_key_exists('task_list_id', $filters) && $filters['task_list_id'] !== null) {
            $query->where('task_list_id', $filters['task_list_id']);
        }

        if (($filters['unlisted'] ?? false) === true) {
            $query->whereNull('task_list_id');
        }

        if (array_key_exists('parent_id', $filters) && $filters['parent_id'] !== null) {
            $query->where('parent_id', $filters['parent_id']);
        } elseif (($filters['roots_only'] ?? true) === true) {
            $query->whereNull('parent_id');
        }

        if (array_key_exists('is_favorite', $filters)) {
            $query->where('is_favorite', (bool) $filters['is_favorite']);
        }

        if ($with !== []) {
            $query->with($with);
        }

        return $query->get();
    }

    public function create(TaskCreateData $dto): Task
    {
        $attributes = $dto->toArray();
        unset($attributes['attachments']);

        if ($dto->priority === null) {
            $attributes['priority'] = $this->nextPriority(
                $dto->task_list_id,
                $dto->parent_id,
            );
        }

        return Task::create($attributes);
    }

    public function update(Task $task, TaskUpdateData $dto): Task
    {
        $attributes = [];

        foreach ([
            'task_list_id',
            'parent_id',
            'title',
            'content',
            'finished_at',
            'is_declined',
            'is_favorite',
            'priority',
        ] as $field) {
            if (!($dto->{$field} instanceof Optional)) {
                $attributes[$field] = $dto->{$field};
            }
        }

        if ($attributes !== []) {
            $task->update($attributes);
        }

        return $task->refresh();
    }

    /**
     * @param list<int> $ids
     */
    public function reorder(array $ids): void
    {
        foreach ($ids as $index => $id) {
            Task::query()
                ->whereKey($id)
                ->update(['priority' => $index]);
        }
    }

    /**
     * @param list<int> $ids
     * @return Collection<int, Task>
     */
    public function findManyOrderedByIds(array $ids): Collection
    {
        $tasks = Task::query()->whereIn('id', $ids)->get();

        return $tasks->sortBy(static fn (Task $task) => array_search($task->id, $ids, true))->values();
    }

    public function delete(Task $task): bool
    {
        return (bool) $task->delete();
    }

    public function nextPriority(?int $taskListId, ?int $parentId): int
    {
        $max = $this->siblingQuery($taskListId, $parentId)->lockForUpdate()->max('priority');

        return $max === null ? 0 : ((int) $max) + 1;
    }

    private function siblingQuery(?int $taskListId, ?int $parentId): Builder
    {
        $query = Task::query();

        if ($parentId !== null) {
            return $query->where('parent_id', $parentId);
        }

        $query->whereNull('parent_id');

        if ($taskListId !== null) {
            return $query->where('task_list_id', $taskListId);
        }

        return $query->whereNull('task_list_id');
    }
}
