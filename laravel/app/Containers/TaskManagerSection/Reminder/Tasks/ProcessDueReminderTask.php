<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderRepository;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessDueReminderTask extends ParentTask
{
    public function __construct(
        private readonly SendReminderNotificationTask $sendReminderNotificationTask,
        private readonly AdvanceReminderAfterNotifyTask $advanceReminderAfterNotifyTask,
        private readonly ReminderRepository $reminderRepository
    ) {
    }

    /**
     * @throws Throwable
     */
    public function run(Reminder $reminder): Reminder
    {
        $claimed = DB::transaction(function () use ($reminder) {
            /** @var Reminder|null $locked */
            $locked = Reminder::withoutGlobalScopes()
                ->whereKey($reminder->id)
                ->where('is_active', true)
                ->whereNotNull('next_remind_at')
                ->where('next_remind_at', '<=', now())
                ->lockForUpdate()
                ->first();

            if ($locked === null) {
                return null;
            }

            // Claim reminder so concurrent workers skip it while notification is sent.
            $locked->next_remind_at = null;
            $this->reminderRepository->save($locked);

            return $locked;
        });

        if ($claimed === null) {
            return $reminder;
        }

        try {
            $this->sendReminderNotificationTask->run($claimed);

            return $this->advanceReminderAfterNotifyTask->run($claimed);
        } catch (Throwable $exception) {
            $claimed->next_remind_at = now();
            $this->reminderRepository->save($claimed);

            throw $exception;
        }
    }
}
