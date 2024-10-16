<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Data\Repositories;

use App\Containers\TaskManagerSection\TaskList\Data\DTO\TaskListCreateData;
use App\Containers\TaskManagerSection\TaskList\Data\DTO\TaskListUpdateData;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use Illuminate\Database\Eloquent\Collection;

class TaskListRepository
{
    /**
     * @return Collection
     */
    public function getTaskLists(): Collection
    {
        return TaskList::with(['tasks.comments.user'])->get();
    }

    /**
     * @param TaskList $taskList
     * @param TaskListUpdateData $dto
     * @return TaskList
     */
    public function updateTaskList(TaskList $taskList, TaskListUpdateData $dto): TaskList
    {
        $taskList->update($dto->toArray());

        return $taskList;
    }

    /**
     * @param TaskListCreateData $dto
     * @return TaskList
     */
    public function createTaskList(TaskListCreateData $dto): TaskList
    {
        return TaskList::create($dto->toArray());
    }

    /**
     * @param TaskList $taskList
     * @return bool|null
     */
    public function deleteTaskList(TaskList $taskList): ?bool
    {
        return $taskList->delete();
    }
}
