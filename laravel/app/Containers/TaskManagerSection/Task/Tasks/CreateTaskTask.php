<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Tasks;

use App\Containers\TaskManagerSection\Task\Data\DTO\TaskCreateData;
use App\Containers\TaskManagerSection\Task\Data\Repositories\TaskRepository;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateTaskTask extends ParentTask
{
    public function __construct(
        private readonly TaskRepository $taskRepository
    )
    {
    }

    /**
     * @param TaskCreateData $dto
     * @return Task
     */
    public function run(TaskCreateData $dto): Task
    {
        return $this->taskRepository->createTask($dto);
    }
}
