<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListRemindersTask extends ParentTask
{
    public function __construct(
        private readonly ReminderRepository $reminderRepository
    ) {
    }

    public function run(): Collection
    {
        return $this->reminderRepository->list();
    }
}
