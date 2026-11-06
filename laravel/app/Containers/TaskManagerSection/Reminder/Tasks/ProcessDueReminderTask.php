<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderOccurrenceRepository;
use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderRepository;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDueReminderTask extends ParentTask
{
    public function __construct(
        private readonly SendReminderNotificationTask $sendReminderNotificationTask,
        private readonly MarkReminderAsNotifiedTask $markReminderAsNotifiedTask,
        private readonly ReminderRepository $reminderRepository,
        private readonly ReminderOccurrenceRepository $occurrenceRepository
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
            return $this->recoverUnclaimed($reminder);
        }

        try {
            $this->sendReminderNotificationTask->run($claimed);

            $claimed->last_reminded_at = now();
            $this->reminderRepository->save($claimed);

            return $this->markReminderAsNotifiedTask->run($claimed)['reminder'];
        } catch (Throwable $exception) {
            $claimed->refresh();

            if ($claimed->last_reminded_at === null) {
                $claimed->next_remind_at = now();
                $this->reminderRepository->save($claimed);

                throw $exception;
            }

            try {
                return $this->markIfUnrecorded($claimed);
            } catch (Throwable $markException) {
                Log::warning('reminder.notify.mark_failed', [
                    'reminder_id' => $claimed->id,
                    'message' => $markException->getMessage(),
                ]);

                throw $markException;
            }
        }
    }

    private function recoverUnclaimed(Reminder $reminder): Reminder
    {
        return DB::transaction(function () use ($reminder) {
            /** @var Reminder|null $fresh */
            $fresh = Reminder::withoutGlobalScopes()
                ->whereKey($reminder->id)
                ->lockForUpdate()
                ->first();

            if ($fresh === null) {
                return $reminder;
            }

            if ($fresh->next_remind_at === null
                && $fresh->last_reminded_at !== null
                && !$this->hasNotifiedOccurrence($fresh)
            ) {
                return $this->markIfUnrecorded($fresh);
            }

            // Claimed, then the worker died before the notification was stored.
            if ($fresh->is_active
                && $fresh->next_remind_at === null
                && $fresh->last_reminded_at === null
            ) {
                $fresh->next_remind_at = now();
                $this->reminderRepository->save($fresh);
            }

            return $fresh;
        });
    }

    /**
     * @throws Throwable
     */
    private function markIfUnrecorded(Reminder $reminder): Reminder
    {
        if ($this->hasNotifiedOccurrence($reminder)) {
            return $reminder;
        }

        return $this->markReminderAsNotifiedTask->run($reminder)['reminder'];
    }

    private function hasNotifiedOccurrence(Reminder $reminder): bool
    {
        if ($reminder->datetime === null) {
            return false;
        }

        return $this->occurrenceRepository->findOpenForSchedule($reminder, $reminder->datetime) !== null;
    }
}
