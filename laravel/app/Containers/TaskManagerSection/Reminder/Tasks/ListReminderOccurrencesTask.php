<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderOccurrenceRepository;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListReminderOccurrencesTask extends ParentTask
{
    public function __construct(
        private readonly ReminderOccurrenceRepository $occurrenceRepository
    ) {
    }

    public function run(Reminder $reminder): Collection
    {
        return $this->occurrenceRepository->listForReminder($reminder);
    }
}
