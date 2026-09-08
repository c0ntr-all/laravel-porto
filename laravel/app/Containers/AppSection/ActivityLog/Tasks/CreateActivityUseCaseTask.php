<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Tasks;

use App\Containers\AppSection\ActivityLog\Data\DTO\UserLogCreateDto;
use App\Containers\AppSection\ActivityLog\Data\Repositories\ActivityUserLogRepository;
use App\Containers\AppSection\ActivityLog\Models\ActivityUseCaseLog;
use App\Ship\Helpers\Correlation;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateActivityUseCaseTask extends ParentTask
{
    public function __construct(
        private readonly ActivityUserLogRepository $activityUserLogRepository
    ) {
    }

    public function run(UserLogCreateDto $dto): ActivityUseCaseLog
    {
        if ($dto->correlation_uuid === '' || $dto->correlation_uuid === null) {
            Correlation::init();
            $dto->correlation_uuid = (string) Correlation::getUuid();
        }

        return $this->activityUserLogRepository->create($dto->toArray());
    }
}
