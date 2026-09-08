<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\UI\API\Transformers;

use App\Containers\AppSection\ActivityLog\Models\ActivityUseCaseLog;
use App\Containers\AppSection\User\UI\Transformer\UserTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class UseCaseLogTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'user',
        'systemLogs',
    ];

    public function transform(ActivityUseCaseLog $useCaseLog): array
    {
        return [
            'id' => $useCaseLog->id,
            'user_id' => $useCaseLog->user_id,
            'correlation_uuid' => $useCaseLog->correlation_uuid,
            'event_type' => $useCaseLog->event_type,
            'event_label' => __('appSection@activityLog::events.' . $useCaseLog->event_type),
            'loggable_id' => $useCaseLog->loggable_id,
            'loggable_type' => $useCaseLog->loggable_type,
            'created_at' => $useCaseLog->created_at->setTimezone('Europe/Moscow')->format('Y-m-d H:i:s'),
        ];
    }

    public function includeUser(ActivityUseCaseLog $useCaseLog): Item
    {
        return $this->item($useCaseLog->user, new UserTransformer(), 'users');
    }

    public function includeSystemLogs(ActivityUseCaseLog $useCaseLog): Collection
    {
        $systemLogs = $useCaseLog->relationLoaded('systemLogs')
            ? $useCaseLog->systemLogs
            : $useCaseLog->systemLogs()->get();

        return $this->collection($systemLogs, new SystemLogTransformer(), 'system-logs');
    }
}
