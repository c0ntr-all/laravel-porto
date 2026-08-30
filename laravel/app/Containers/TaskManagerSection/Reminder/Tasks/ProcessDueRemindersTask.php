<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Jobs\SendReminderNotificationJob;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;

class ProcessDueRemindersTask extends ParentTask
{
    public function __construct(
        private readonly FindDueRemindersTask $findDueRemindersTask
    ) {
    }

    /**
     * @return int Number of reminders dispatched
     */
    public function run(?Carbon $at = null, int $limit = 100): int
    {
        $dueReminders = $this->findDueRemindersTask->run($at, $limit);

        foreach ($dueReminders as $reminder) {
            SendReminderNotificationJob::dispatch($reminder->id);
        }

        return $dueReminders->count();
    }
}
