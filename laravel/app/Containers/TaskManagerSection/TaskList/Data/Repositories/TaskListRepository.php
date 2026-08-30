<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Data\Repositories;

use App\Containers\TaskManagerSection\TaskList\Data\DTO\TaskListCreateData;
use App\Containers\TaskManagerSection\TaskList\Data\DTO\TaskListUpdateData;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;

class TaskListRepository
{
    /**
     * @param list<string> $with
     */
    public function list(array $with = []): Collection
    {
        $query = TaskList::query()->orderByDesc('id');

        if ($with !== []) {
            $query->with($with);
        }

        return $query->get();
    }

    public function find(TaskList $taskList, array $with = []): TaskList
    {
        if ($with !== []) {
            $taskList->load($with);
        }

        return $taskList;
    }

    public function create(TaskListCreateData $dto): TaskList
    {
        return TaskList::create($dto->toArray());
    }

    public function update(TaskList $taskList, TaskListUpdateData $dto): TaskList
    {
        $attributes = [];

        if (!($dto->title instanceof Optional)) {
            $attributes['title'] = $dto->title;
        }

        if ($attributes !== []) {
            $taskList->update($attributes);
        }

        return $taskList->refresh();
    }

    public function delete(TaskList $taskList): bool
    {
        return (bool) $taskList->delete();
    }
}
