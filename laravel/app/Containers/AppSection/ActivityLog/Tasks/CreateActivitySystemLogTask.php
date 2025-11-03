<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Tasks;

use App\Containers\AppSection\ActivityLog\Data\DTO\SystemLogCreateDto;
use App\Containers\AppSection\ActivityLog\Data\Repositories\ActivitySystemLogRepository;
use App\Containers\AppSection\ActivityLog\Models\ActivitySystemLog;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateActivitySystemLogTask extends ParentTask
{
    public function __construct(
        private readonly ActivitySystemLogRepository $activitySystemLogRepository
    )
    {
    }

    /**
     * @param SystemLogCreateDto $dto
     * @return ActivitySystemLog
     */
    public function run(SystemLogCreateDto $dto): ActivitySystemLog
    {
        return $this->activitySystemLogRepository->create($dto->toArray());
    }
}
