<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderRepository;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Services\ReminderScheduleCalculator;
use App\Ship\Parents\Tasks\Task as ParentTask;

/**
 * Advances recurring schedule to the next cycle, or deactivates a one-shot reminder.
 */
class AdvanceReminderScheduleTask extends ParentTask
{
    public function __construct(
        private readonly ReminderScheduleCalculator $scheduleCalculator,
        private readonly ReminderRepository $reminderRepository
    ) {
    }

    public function run(Reminder $reminder): Reminder
    {
        if ($reminder->hasRecurrence() && $reminder->datetime !== null) {
            $reminder->datetime = $this->scheduleCalculator->advanceEventAt(
                $reminder->datetime,
                (int) $reminder->interval_value,
                $reminder->interval_unit
            );

            $reminder->next_remind_at = $reminder->is_active
                ? $this->scheduleCalculator->calculateNextRemindAt(
                    $reminder->datetime,
                    $reminder->to_remind_before_value,
                    $reminder->to_remind_before_unit
                )
                : null;
        } else {
            $reminder->is_active = false;
            $reminder->next_remind_at = null;
        }

        return $this->reminderRepository->save($reminder);
    }
}
