<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\Actions;

use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Tasks\GetReminderByTaskTask;
use App\Containers\TaskManagerSection\Reminder\UI\API\Requests\ReminderGetRequest;
use App\Containers\TaskManagerSection\Reminder\UI\API\Transformers\ReminderTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetReminderAction extends BaseAction
{
    public function __construct(
        private readonly GetReminderByTaskTask $getReminderByTaskTask
    ) {
    }

    public function handle(Task $task): Reminder
    {
        return $this->getReminderByTaskTask->run($task);
    }

    public function asController(Task $task, ReminderGetRequest $request): JsonResponse
    {
        $reminder = $this->handle($task);

        return fractal($reminder, new ReminderTransformer())
            ->withResourceName('reminders')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
