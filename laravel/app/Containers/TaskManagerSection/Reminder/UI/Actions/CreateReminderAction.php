<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\Actions;

use App\Containers\TaskManagerSection\Reminder\Data\DTO\ReminderCreateData;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Tasks\CreateReminderTask;
use App\Containers\TaskManagerSection\Reminder\UI\API\Requests\ReminderCreateRequest;
use App\Containers\TaskManagerSection\Reminder\UI\API\Transformers\ReminderTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class CreateReminderAction extends BaseAction
{
    public function __construct(
        private readonly CreateReminderTask $createReminderTask
    ) {
    }

    public function handle(ReminderCreateData $dto): Reminder
    {
        return $this->createReminderTask->run($dto);
    }

    public function asController(ReminderCreateRequest $request, Task $task): JsonResponse
    {
        $dto = ReminderCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;
        $dto->task_id = $task->id;

        $reminder = $this->handle($dto);

        return fractal($reminder, new ReminderTransformer())
            ->withResourceName('reminders')
            ->addMeta(['message' => 'New reminder successfully created!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
