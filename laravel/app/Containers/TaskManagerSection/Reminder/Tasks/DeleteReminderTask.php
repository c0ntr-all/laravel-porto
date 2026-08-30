<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderRepository;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteReminderTask extends ParentTask
{
    public function __construct(
        private readonly ReminderRepository $reminderRepository
    ) {
    }

    public function run(Reminder $reminder): bool
    {
        return $this->reminderRepository->delete($reminder);
    }
}
