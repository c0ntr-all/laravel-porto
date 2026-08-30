<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class FindDueRemindersTask extends ParentTask
{
    public function __construct(
        private readonly ReminderRepository $reminderRepository
    ) {
    }

    public function run(?Carbon $at = null, int $limit = 100): Collection
    {
        return $this->reminderRepository->findDue($at ?? now(), $limit);
    }
}
