<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Tasks;

use App\Containers\AppSection\ActivityLog\Data\DTO\UserLogCreateDto;
use App\Containers\AppSection\ActivityLog\Data\Repositories\ActivityUserLogRepository;
use App\Containers\AppSection\ActivityLog\Models\ActivitySystemLog;
use App\Containers\AppSection\ActivityLog\Models\ActivityUserLog;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class CreateActivityUserLogTask extends ParentTask
{
    public function __construct(
        private readonly ActivityUserLogRepository $activityUserLogRepository
    )
    {
    }

    /**
     * @param Collection $systemLogs
     * @return ActivityUserLog
     */
    public function run(Collection $systemLogs): ActivityUserLog
    {
        $firstLog = $systemLogs->first();

        $text = $this->buildText($systemLogs);

        $userLogCreateDto = UserLogCreateDto::from([
            'user_id' => $firstLog->user_id,
            'correlation_uuid' => $firstLog->correlation_uuid,
            'loggable_type' => $firstLog->main_type,
            'loggable_id' => $firstLog->main_id,
            'text' => $text,
        ]);

        return $this->activityUserLogRepository->create($userLogCreateDto->toArray());
    }

    private function buildText($systemLogs): string
    {
        $counts = [];

        $systemLogs->each(function (ActivitySystemLog $log) use (&$counts) {
            $modelType = $log->related_type ?? $log->main_type;
            $fullEventName = $modelType . '.' . $log->event_type;
            $counts[$fullEventName] = ($counts[$fullEventName] ?? 0) + 1;
        });

        $lines = [];
        foreach ($counts as $eventType => $count) {
            $eventTypeParts = explode('.', $eventType); //0 - container alias, 1 - event
            $eventEnum = EventTypesEnum::from($eventTypeParts[1]);
            $containerEnum = ContainerAliasEnum::from($eventTypeParts[0]);

            $fullMessage = $eventEnum->getEventMessage() . ' ' . $containerEnum->getContainerMessage();

            $lines[] = $count > 1
                ? "{$fullMessage} ({$count} шт.)"
                : $fullMessage;
        }

        return implode("; ", $lines);
    }
}
