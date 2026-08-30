<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;

/**
 * @deprecated Use MarkReminderAsNotifiedTask for notify flow and
 *             AdvanceReminderScheduleTask / CompleteReminderTask for schedule advance.
 */
class AdvanceReminderAfterNotifyTask extends ParentTask
{
    public function __construct(
        private readonly MarkReminderAsNotifiedTask $markReminderAsNotifiedTask
    ) {
    }

    public function run(Reminder $reminder, ?Carbon $notifiedAt = null): Reminder
    {
        return $this->markReminderAsNotifiedTask->run($reminder, $notifiedAt)['reminder'];
    }
}
