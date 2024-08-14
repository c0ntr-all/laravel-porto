<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Tasks;

use App\Containers\TaskManagerSection\TaskList\Data\Repositories\TaskListRepository;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Ship\Parents\Tasks\Task;

class DeleteTaskListTask extends Task
{
    public function __construct(
        private readonly TaskListRepository $taskListRepository
    )
    {
    }

    /**
     * @param TaskList $taskList
     * @return bool
     */
    public function run(TaskList $taskList): bool
    {
        return $this->taskListRepository->deleteTaskList($taskList);
    }
}
