<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderOccurrenceRepository;
use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderRepository;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Models\ReminderOccurrence;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

class CompleteReminderTask extends ParentTask
{
    public function __construct(
        private readonly ReminderOccurrenceRepository $occurrenceRepository,
        private readonly ReminderRepository $reminderRepository,
        private readonly AdvanceReminderScheduleTask $advanceReminderScheduleTask
    ) {
    }

    /**
     * @return array{reminder: Reminder, occurrence: ReminderOccurrence}
     *
     * @throws Throwable
     */
    public function run(Reminder $reminder, ?Carbon $completedAt = null): array
    {
        if (!$reminder->canBeCompleted()) {
            throw new UnprocessableEntityHttpException(
                'Reminder cannot be completed: it is inactive or has no scheduled datetime.'
            );
        }

        $completedAt ??= now();

        return DB::transaction(function () use ($reminder, $completedAt) {
            /** @var Reminder $locked */
            $locked = Reminder::query()
                ->whereKey($reminder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$locked->canBeCompleted()) {
                throw new UnprocessableEntityHttpException(
                    'Reminder cannot be completed: it is inactive or has no scheduled datetime.'
                );
            }

            $scheduledAt = $locked->datetime;
            $isOverdue = $completedAt->gt($scheduledAt);

            $openOccurrence = $this->occurrenceRepository->findOpenForSchedule($locked, $scheduledAt);

            if ($openOccurrence !== null) {
                $occurrence = $this->occurrenceRepository->markCompleted(
                    $openOccurrence,
                    $completedAt,
                    $isOverdue
                );
            } else {
                $occurrence = $this->occurrenceRepository->createCompleted(
                    $locked,
                    $scheduledAt,
                    $completedAt,
                    $isOverdue,
                    $locked->last_reminded_at
                );
            }

            $locked->last_completed_at = $completedAt;
            $this->reminderRepository->save($locked);

            $locked = $this->advanceReminderScheduleTask->run($locked);

            return [
                'reminder' => $locked->load(['task', 'occurrences' => fn ($q) => $q->limit(20)]),
                'occurrence' => $occurrence->fresh(),
            ];
        });
    }
}
