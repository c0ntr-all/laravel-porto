<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\API\Transformers;

use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use League\Fractal\TransformerAbstract;

class ReminderTransformer extends TransformerAbstract
{
    public function transform(Reminder $reminder): array
    {
        return [
            'id' => $reminder->id,
            'task_id' => $reminder->task_id,
            'user_id' => $reminder->user_id,
            'is_active' => $reminder->is_active,
            'interval' => $reminder->interval,
            'to_remind_before' => $reminder->to_remind_before,
            'datetime' => $reminder->datetime->format('Y-m-d H:i:s'),
            'created_at' => $reminder->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $reminder->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
