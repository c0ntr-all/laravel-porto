<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Tasks;

use App\Containers\TaskManagerSection\TaskList\Data\DTO\TaskListCreateData;
use App\Containers\TaskManagerSection\TaskList\Data\Repositories\TaskListRepository;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateTaskListTask extends ParentTask
{
    public function __construct(
        private readonly TaskListRepository $taskListRepository
    ) {
    }

    public function run(TaskListCreateData $dto): TaskList
    {
        return $this->taskListRepository->create($dto);
    }
}
