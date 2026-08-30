<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetReminderByTaskTask extends ParentTask
{
    public function run(Task $task): Reminder
    {
        $reminder = $task->reminder;

        if ($reminder === null) {
            throw new NotFoundHttpException('Reminder not found for this task.');
        }

        return $reminder->loadMissing('task');
    }
}
