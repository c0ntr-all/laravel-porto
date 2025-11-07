<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Tasks;

use App\Containers\AppSection\ActivityLog\Data\DTO\UserLogCreateDto;
use App\Containers\AppSection\ActivityLog\Data\Repositories\ActivityUserLogRepository;
use App\Containers\AppSection\ActivityLog\Models\ActivityUseCaseLog;
use App\Ship\Helpers\Correlation;
use App\Ship\Models\ActivityLoggableModel;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateActivityUseCaseTask extends ParentTask
{
    public function __construct(
        private readonly ActivityUserLogRepository $activityUserLogRepository
    )
    {
    }

    /**
     * @param ActivityLoggableModel $model
     * @param string $eventTypesValue
     * @return ActivityUseCaseLog
     */
    public function run(ActivityLoggableModel $model, string $eventTypesValue): ActivityUseCaseLog
    {
        $correlationUuid = Correlation::getUuid();

        $userLogCreateDto = UserLogCreateDto::from([
            'user_id' => $model->user_id,
            'correlation_uuid' => $correlationUuid,
            'loggable_type' => $model->getLoggableType(),
            'loggable_id' => $model->id,
            'event_type' => $eventTypesValue,
        ]);

        return $this->activityUserLogRepository->create($userLogCreateDto->toArray());
    }
}
