<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Tasks;

use App\Containers\TaskManagerSection\Task\Data\DTO\TaskUpdateData;
use App\Containers\TaskManagerSection\Task\Data\Repositories\TaskRepository;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateTaskTask extends ParentTask
{
    public function __construct(
        private readonly TaskRepository $taskRepository
    )
    {
    }

    /**
     * @param Task $task
     * @param TaskUpdateData $dto
     * @return Task
     */
    public function run(Task $task, TaskUpdateData $dto): Task
    {
        return $this->taskRepository->update($task, $dto);
    }
}
