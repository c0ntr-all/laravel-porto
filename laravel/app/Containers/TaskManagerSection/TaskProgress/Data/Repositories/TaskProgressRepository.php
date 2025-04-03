<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\Data\Repositories;

use App\Containers\TaskManagerSection\TaskProgress\Models\TaskProgress;
use App\Containers\TaskManagerSection\TaskProgress\Data\DTO\TaskProgressCreateData;
use App\Containers\TaskManagerSection\TaskProgress\Data\DTO\TaskProgressUpdateData;

class TaskProgressRepository
{
    /**
     * @param TaskProgressCreateData $dto
     * @return mixed
     */
    public function create(TaskProgressCreateData $dto): TaskProgress
    {
        return TaskProgress::create($dto->toArray());
    }

    /**
     * @param TaskProgress $taskProgress
     * @param TaskProgressUpdateData $dto
     * @return TaskProgress
     */
    public function update(TaskProgress $taskProgress, TaskProgressUpdateData $dto): TaskProgress
    {
        $taskProgress->update($dto->toArray());

        return $taskProgress;
    }
}
