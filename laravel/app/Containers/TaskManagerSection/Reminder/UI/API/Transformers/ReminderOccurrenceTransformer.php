<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\API\Transformers;

use App\Containers\TaskManagerSection\Reminder\Models\ReminderOccurrence;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class ReminderOccurrenceTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'reminder',
    ];

    public function transform(ReminderOccurrence $occurrence): array
    {
        return [
            'id' => (string) $occurrence->id,
            'reminder_id' => (string) $occurrence->reminder_id,
            'task_id' => (string) $occurrence->task_id,
            'status' => $occurrence->status->value,
            'scheduled_at' => $occurrence->scheduled_at->format('Y-m-d H:i:s'),
            'notified_at' => $occurrence->notified_at?->format('Y-m-d H:i:s'),
            'completed_at' => $occurrence->completed_at?->format('Y-m-d H:i:s'),
            'is_overdue' => (bool) $occurrence->is_overdue,
            'created_at' => $occurrence->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $occurrence->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeReminder(ReminderOccurrence $occurrence): ?Item
    {
        if (!$occurrence->relationLoaded('reminder') || $occurrence->reminder === null) {
            return null;
        }

        return $this->item($occurrence->reminder, new ReminderTransformer(), 'reminders');
    }
}
