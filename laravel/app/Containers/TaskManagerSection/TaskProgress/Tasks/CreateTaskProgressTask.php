<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\Tasks;

use App\Containers\TaskManagerSection\TaskProgress\Data\DTO\TaskProgressCreateData;
use App\Containers\TaskManagerSection\TaskProgress\Data\Repositories\TaskProgressRepository;
use App\Containers\TaskManagerSection\TaskProgress\Models\TaskProgress;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateTaskProgressTask extends ParentTask
{
    public function __construct(
        private readonly TaskProgressRepository $taskProgressRepository
    )
    {
    }

    /**
     * @param TaskProgressCreateData $dto
     * @return TaskProgress
     */
    public function run(TaskProgressCreateData $dto): TaskProgress
    {
        return $this->taskProgressRepository->create($dto);
    }
}
