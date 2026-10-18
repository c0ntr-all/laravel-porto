<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Tasks;

use App\Containers\TaskManagerSection\Task\Data\Repositories\TaskRepository;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteTaskTask extends ParentTask
{
    public function __construct(
        private readonly TaskRepository $taskRepository
    ) {
    }

    public function run(Task $task): bool
    {
        $task->subtasks()->each(function (Task $subtask): void {
            $this->taskRepository->delete($subtask);
        });

        return $this->taskRepository->delete($task);
    }
}
