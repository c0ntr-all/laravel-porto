<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Tasks;

use App\Containers\TaskManagerSection\TaskList\Data\DTO\TaskListUpdateData;
use App\Containers\TaskManagerSection\TaskList\Data\Repositories\TaskListRepository;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Ship\Parents\Tasks\Task;

class UpdateTaskListTask extends Task
{
    public function __construct(
        private readonly TaskListRepository $taskListRepository
    )
    {
    }

    /**
     * @param TaskList $taskList
     * @param TaskListUpdateData $dto
     * @return TaskList
     */
    public function run(TaskList $taskList, TaskListUpdateData $dto): TaskList
    {
        return $this->taskListRepository->updateTaskList($taskList, $dto);
    }
}
