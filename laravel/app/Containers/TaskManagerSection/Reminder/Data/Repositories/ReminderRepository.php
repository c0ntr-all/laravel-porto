<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Data\Repositories;

use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Data\DTO\ReminderCreateData;
use App\Containers\TaskManagerSection\Reminder\Data\DTO\ReminderUpdateData;

class ReminderRepository
{
    /**
     * @param ReminderCreateData $dto
     * @return mixed
     */
    public function create(ReminderCreateData $dto): Reminder
    {
        return Reminder::create($dto->toArray());
    }

    /**
     * @param Reminder $taskProgress
     * @param ReminderUpdateData $dto
     * @return Reminder
     */
    public function update(Reminder $taskProgress, ReminderUpdateData $dto): Reminder
    {
        $taskProgress->update($dto->toArray());

        return $taskProgress;
    }
}
