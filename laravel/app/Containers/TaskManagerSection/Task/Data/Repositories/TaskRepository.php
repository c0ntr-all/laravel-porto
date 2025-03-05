<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Data\Repositories;

use App\Containers\TaskManagerSection\Task\Data\DTO\TaskCreateData;
use App\Containers\TaskManagerSection\Task\Data\DTO\TaskUpdateData;
use App\Containers\TaskManagerSection\Task\Models\Task;

class TaskRepository
{
    /**
     * @param TaskCreateData $dto
     * @return mixed
     */
    public function createTask(TaskCreateData $dto): Task
    {
        return Task::create($dto->toArray());
    }

    /**
     * @param Task $task
     * @param TaskUpdateData $dto
     * @return Task
     */
    public function update(Task $task, TaskUpdateData $dto): Task
    {
        $task->update($dto->toArray());

        return $task;
    }

    /**
     * @param Task $task
     * @return bool|null
     */
    public function deleteTask(Task $task): ?bool
    {
        return $task->delete();
    }
}
