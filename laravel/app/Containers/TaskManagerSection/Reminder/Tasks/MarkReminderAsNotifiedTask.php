<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderOccurrenceRepository;
use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderRepository;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Models\ReminderOccurrence;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;

/**
 * Records that a due reminder notification was sent.
 * Recurring reminders wait for user completion before the schedule advances.
 * One-shot reminders are deactivated after notify.
 */
class MarkReminderAsNotifiedTask extends ParentTask
{
    public function __construct(
        private readonly ReminderRepository $reminderRepository,
        private readonly ReminderOccurrenceRepository $occurrenceRepository
    ) {
    }

    /**
     * @return array{reminder: Reminder, occurrence: ReminderOccurrence}
     */
    public function run(Reminder $reminder, ?Carbon $notifiedAt = null): array
    {
        $notifiedAt ??= now();

        $occurrence = $this->occurrenceRepository->createNotified($reminder, $notifiedAt);

        $reminder->last_reminded_at = $notifiedAt;
        // Keep next_remind_at null (already claimed) — recurring awaits completion.
        $reminder->next_remind_at = null;

        if (!$reminder->hasRecurrence()) {
            $reminder->is_active = false;
        }

        $this->reminderRepository->save($reminder);

        return [
            'reminder' => $reminder,
            'occurrence' => $occurrence,
        ];
    }
}
