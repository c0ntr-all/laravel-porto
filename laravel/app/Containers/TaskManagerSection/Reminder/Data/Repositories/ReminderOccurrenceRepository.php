<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Data\Repositories;

use App\Containers\TaskManagerSection\Reminder\Enums\ReminderOccurrenceStatusEnum;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Models\ReminderOccurrence;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ReminderOccurrenceRepository
{
    public function listForReminder(Reminder $reminder): Collection
    {
        return $reminder->occurrences()->get();
    }

    public function findOpenForSchedule(Reminder $reminder, Carbon $scheduledAt): ?ReminderOccurrence
    {
        return ReminderOccurrence::query()
            ->where('reminder_id', $reminder->id)
            ->where('status', ReminderOccurrenceStatusEnum::NOTIFIED)
            ->where('scheduled_at', $scheduledAt->format('Y-m-d H:i:s'))
            ->latest('id')
            ->first();
    }

    public function createNotified(Reminder $reminder, Carbon $notifiedAt): ReminderOccurrence
    {
        $scheduledAt = $reminder->datetime ?? $notifiedAt;

        return ReminderOccurrence::create([
            'user_id' => $reminder->user_id,
            'reminder_id' => $reminder->id,
            'task_id' => $reminder->task_id,
            'status' => ReminderOccurrenceStatusEnum::NOTIFIED,
            'scheduled_at' => $scheduledAt,
            'notified_at' => $notifiedAt,
            'completed_at' => null,
            'is_overdue' => $notifiedAt->gt($scheduledAt),
        ]);
    }

    public function markCompleted(
        ReminderOccurrence $occurrence,
        Carbon $completedAt,
        bool $isOverdue
    ): ReminderOccurrence {
        $occurrence->status = ReminderOccurrenceStatusEnum::COMPLETED;
        $occurrence->completed_at = $completedAt;
        $occurrence->is_overdue = $isOverdue;
        $occurrence->save();

        return $occurrence;
    }

    public function createCompleted(
        Reminder $reminder,
        Carbon $scheduledAt,
        Carbon $completedAt,
        bool $isOverdue,
        ?Carbon $notifiedAt = null
    ): ReminderOccurrence {
        return ReminderOccurrence::create([
            'user_id' => $reminder->user_id,
            'reminder_id' => $reminder->id,
            'task_id' => $reminder->task_id,
            'status' => ReminderOccurrenceStatusEnum::COMPLETED,
            'scheduled_at' => $scheduledAt,
            'notified_at' => $notifiedAt,
            'completed_at' => $completedAt,
            'is_overdue' => $isOverdue,
        ]);
    }
}
