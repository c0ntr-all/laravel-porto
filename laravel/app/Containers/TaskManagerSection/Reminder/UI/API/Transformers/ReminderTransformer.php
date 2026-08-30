<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\API\Transformers;

use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class ReminderTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'task',
    ];

    public function transform(Reminder $reminder): array
    {
        return [
            'id' => (string) $reminder->id,
            'task_id' => (string) $reminder->task_id,
            'user_id' => (string) $reminder->user_id,
            'is_active' => (bool) $reminder->is_active,
            'datetime' => $reminder->datetime?->format('Y-m-d H:i:s'),
            'interval' => $reminder->interval_value && $reminder->interval_unit
                ? [
                    'value' => $reminder->interval_value,
                    'unit' => $reminder->interval_unit->value,
                ]
                : null,
            'to_remind_before' => $reminder->to_remind_before_value && $reminder->to_remind_before_unit
                ? [
                    'value' => $reminder->to_remind_before_value,
                    'unit' => $reminder->to_remind_before_unit->value,
                ]
                : null,
            'next_remind_at' => $reminder->next_remind_at?->format('Y-m-d H:i:s'),
            'last_reminded_at' => $reminder->last_reminded_at?->format('Y-m-d H:i:s'),
            'created_at' => $reminder->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $reminder->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeTask(Reminder $reminder): ?Item
    {
        if (!$reminder->relationLoaded('task') || $reminder->task === null) {
            return null;
        }

        return $this->item($reminder->task, new TaskTransformer(), 'tasks');
    }
}
