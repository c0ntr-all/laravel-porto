<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Data\DTO\ReminderUpdateData;
use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderRepository;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateReminderTask extends ParentTask
{
    public function __construct(
        private readonly ReminderRepository $reminderRepository
    ) {
    }

    public function run(Reminder $reminder, ReminderUpdateData $dto): Reminder
    {
        return $this->reminderRepository->update($reminder, $dto);
    }
}
