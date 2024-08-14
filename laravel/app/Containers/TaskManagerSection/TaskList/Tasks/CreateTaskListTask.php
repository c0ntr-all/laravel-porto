<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Tasks;

use App\Containers\TaskManagerSection\TaskList\Data\DTO\TaskListCreateData;
use App\Containers\TaskManagerSection\TaskList\Data\Repositories\TaskListRepository;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Ship\Parents\Tasks\Task;

class CreateTaskListTask extends Task
{
    public function __construct(
        private readonly TaskListRepository $taskListRepository
    )
    {
    }

    /**
     * @param TaskListCreateData $dto
     * @return TaskList
     */
    public function run(TaskListCreateData $dto): TaskList
    {
        return $this->taskListRepository->createTaskList($dto);
    }
}
