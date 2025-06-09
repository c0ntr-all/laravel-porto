<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Data\DTO\ReminderCreateData;
use App\Containers\TaskManagerSection\Reminder\Data\Repositories\ReminderRepository;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateReminderTask extends ParentTask
{
    public function __construct(
        private readonly ReminderRepository $taskProgressRepository
    )
    {
    }

    /**
     * @param ReminderCreateData $dto
     * @return Reminder
     */
    public function run(ReminderCreateData $dto): Reminder
    {
        return $this->taskProgressRepository->create($dto);
    }
}
