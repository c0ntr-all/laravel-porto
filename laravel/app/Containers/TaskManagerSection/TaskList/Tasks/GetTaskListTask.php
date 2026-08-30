<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Tasks;

use App\Containers\TaskManagerSection\TaskList\Data\Repositories\TaskListRepository;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Ship\Parents\Tasks\Task as ParentTask;

class GetTaskListTask extends ParentTask
{
    public function __construct(
        private readonly TaskListRepository $taskListRepository
    ) {
    }

    /**
     * @param list<string> $with
     */
    public function run(TaskList $taskList, array $with = []): TaskList
    {
        return $this->taskListRepository->find($taskList, $with);
    }
}
