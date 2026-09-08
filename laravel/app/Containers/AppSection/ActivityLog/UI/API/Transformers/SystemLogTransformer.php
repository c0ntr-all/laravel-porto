<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\UI\API\Transformers;

use App\Containers\AppSection\ActivityLog\Models\ActivitySystemLog;
use League\Fractal\TransformerAbstract;

class SystemLogTransformer extends TransformerAbstract
{
    public function transform(ActivitySystemLog $systemLog): array
    {
        return [
            'id' => $systemLog->id,
            'correlation_uuid' => $systemLog->correlation_uuid,
            'event_type' => $systemLog->event_type,
            'event_label' => __('appSection@activityLog::events.' . $systemLog->event_type),
            'main_type' => $systemLog->main_type,
            'main_id' => $systemLog->main_id,
            'related_type' => $systemLog->related_type,
            'related_id' => $systemLog->related_id,
            'metadata' => $systemLog->metadata ?? [],
            'created_at' => $systemLog->created_at->setTimezone('Europe/Moscow')->format('Y-m-d H:i:s'),
        ];
    }
}
