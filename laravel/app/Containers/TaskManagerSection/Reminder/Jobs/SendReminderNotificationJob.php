<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Jobs;

use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Tasks\ProcessDueReminderTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminderNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $reminderId
    ) {
    }

    public function handle(ProcessDueReminderTask $processDueReminderTask): void
    {
        $reminder = Reminder::withoutGlobalScopes()->find($this->reminderId);

        if ($reminder === null) {
            return;
        }

        $processDueReminderTask->run($reminder);
    }
}
