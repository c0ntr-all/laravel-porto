<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Data\Repositories;

use App\Containers\TaskManagerSection\Task\Data\DTO\TaskCreateData;
use App\Containers\TaskManagerSection\Task\Data\DTO\TaskUpdateData;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;

class TaskRepository
{
    /**
     * @param array{task_list_id?: int|null, unlisted?: bool} $filters
     * @param list<string> $with
     */
    public function list(array $filters = [], array $with = []): Collection
    {
        $query = Task::query()->orderByDesc('id');

        if (array_key_exists('task_list_id', $filters) && $filters['task_list_id'] !== null) {
            $query->where('task_list_id', $filters['task_list_id']);
        }

        if (($filters['unlisted'] ?? false) === true) {
            $query->whereNull('task_list_id');
        }

        if ($with !== []) {
            $query->with($with);
        }

        return $query->get();
    }

    public function create(TaskCreateData $dto): Task
    {
        return Task::create($dto->toArray());
    }

    public function update(Task $task, TaskUpdateData $dto): Task
    {
        $attributes = [];

        foreach (['task_list_id', 'title', 'content', 'finished_at', 'is_declined'] as $field) {
            if (!($dto->{$field} instanceof Optional)) {
                $attributes[$field] = $dto->{$field};
            }
        }

        if ($attributes !== []) {
            $task->update($attributes);
        }

        return $task->refresh();
    }

    public function delete(Task $task): bool
    {
        return (bool) $task->delete();
    }
}
