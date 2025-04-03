<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\Tasks;

use App\Containers\TaskManagerSection\TaskProgress\Data\DTO\TaskProgressUpdateData;
use App\Containers\TaskManagerSection\TaskProgress\Data\Repositories\TaskProgressRepository;
use App\Containers\TaskManagerSection\TaskProgress\Models\TaskProgress;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateTaskProgressTask extends ParentTask
{
    public function __construct(
        private readonly TaskProgressRepository $taskProgressRepository
    )
    {
    }

    /**
     * @param TaskProgress $taskiProgress
     * @param TaskProgressUpdateData $dto
     * @return TaskProgress
     */
    public function run(TaskProgress $taskProgress, TaskProgressUpdateData $dto): TaskProgress
    {
        return $this->taskProgressRepository->update($taskProgress, $dto);
    }
}
