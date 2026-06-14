<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\Actions;

use App\Containers\TaskManagerSection\Reminder\Data\DTO\ReminderCreateData;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Tasks\CreateReminderTask;
use App\Containers\TaskManagerSection\Reminder\UI\API\Requests\ReminderCreateRequest;
use App\Containers\TaskManagerSection\Reminder\UI\API\Transformers\ReminderTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;

class CreateReminderAction extends BaseAction
{

    public function __construct(
        private readonly CreateReminderTask $createReminderTask
    )
    {
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

        $task = $this->handle($dto);

        return fractal($task, new ReminderTransformer())
            ->withResourceName('reminder')
            ->addMeta(['message' => 'New Reminder successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
