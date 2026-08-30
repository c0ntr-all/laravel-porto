<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\Actions;

use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Tasks\DeleteReminderTask;
use App\Containers\TaskManagerSection\Reminder\Tasks\GetReminderByTaskTask;
use App\Containers\TaskManagerSection\Reminder\UI\API\Requests\ReminderDeleteRequest;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteReminderAction extends BaseAction
{
    public function __construct(
        private readonly GetReminderByTaskTask $getReminderByTaskTask,
        private readonly DeleteReminderTask $deleteReminderTask
    ) {
    }

    public function handle(Reminder $reminder): bool
    {
        return $this->deleteReminderTask->run($reminder);
    }

    public function asController(Task $task, ReminderDeleteRequest $request): JsonResponse
    {
        $reminder = $this->getReminderByTaskTask->run($task);
        $this->handle($reminder);

        return response()->json([
            'meta' => [
                'message' => 'Reminder successfully deleted!',
            ],
        ]);
    }
}
