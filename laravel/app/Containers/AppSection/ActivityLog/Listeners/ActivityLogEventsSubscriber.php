<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Listeners;

use App\Containers\AppSection\ActivityLog\Data\DTO\SystemLogCreateDto;
use App\Containers\AppSection\ActivityLog\Data\DTO\UserLogCreateDto;
use App\Containers\AppSection\ActivityLog\Tasks\CreateActivitySystemLogTask;
use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Ship\Contracts\RecordsActivity;
use App\Ship\Events\UseCaseCompleted;
use App\Ship\Helpers\Correlation;
use Illuminate\Events\Dispatcher;

class ActivityLogEventsSubscriber
{
    public function __construct(
        private readonly CreateActivitySystemLogTask $createActivitySystemLogTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask,
    ) {
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(RecordsActivity::class, $this->handleDomainActivity(...));
        $events->listen(UseCaseCompleted::class, $this->handleUseCaseCompleted(...));
    }

    public function handleDomainActivity(RecordsActivity $event): void
    {
        if (Correlation::getUuid() === null) {
            Correlation::init();
        }

        $uuid = Correlation::getUuid();

        if ($uuid === null) {
            return;
        }

        $this->createActivitySystemLogTask->run(SystemLogCreateDto::from([
            'user_id' => $event->activityUserId(),
            'event_type' => $event->activityEventType(),
            'main_type' => $event->activityMainType(),
            'main_id' => $event->activityMainId(),
            'related_type' => $event->activityRelatedType(),
            'related_id' => $event->activityRelatedId(),
            'correlation_uuid' => $uuid,
            'metadata' => $event->activityMetadata(),
        ]));
    }

    public function handleUseCaseCompleted(UseCaseCompleted $event): void
    {
        $uuid = Correlation::getUuid();

        if ($uuid === null) {
            Correlation::init();
            $uuid = Correlation::getUuid();
        }

        $this->createActivityUseCaseTask->run(UserLogCreateDto::from([
            'user_id' => $event->userId,
            'correlation_uuid' => $uuid,
            'loggable_type' => $event->loggableType,
            'loggable_id' => $event->loggableId,
            'event_type' => $event->eventType,
        ]));
    }
}
