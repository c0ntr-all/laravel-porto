<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\Actions;

use App\Containers\TaskManagerSection\Reminder\Data\DTO\ReminderCreateData;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Tasks\CreateReminderTask;
use App\Containers\TaskManagerSection\Reminder\UI\API\Requests\ReminderCreateRequest;
use App\Containers\TaskManagerSection\Reminder\UI\API\Transformers\ReminderTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class CreateReminderAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_REMINDER;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly CreateReminderTask $createReminderTask
    ) {
        parent::__construct();
    }

    public function handle(ReminderCreateData $dto): Reminder
    {
        $reminder = $this->createReminderTask->run($dto);

        $this->recordUseCase($reminder);

        return $reminder;
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
