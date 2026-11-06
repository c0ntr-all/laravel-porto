<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\Actions;

use App\Containers\TaskManagerSection\Reminder\Data\DTO\ReminderUpdateData;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Tasks\GetReminderByTaskTask;
use App\Containers\TaskManagerSection\Reminder\Tasks\UpdateReminderTask;
use App\Containers\TaskManagerSection\Reminder\UI\API\Requests\ReminderUpdateRequest;
use App\Containers\TaskManagerSection\Reminder\UI\API\Transformers\ReminderTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class UpdateReminderAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_REMINDER;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly GetReminderByTaskTask $getReminderByTaskTask,
        private readonly UpdateReminderTask $updateReminderTask
    ) {
        parent::__construct();
    }

    public function handle(Reminder $reminder, ReminderUpdateData $dto): Reminder
    {
        $reminder = $this->updateReminderTask->run($reminder, $dto);

        $this->recordUseCase($reminder);

        return $reminder;
    }

    public function asController(Task $task, ReminderUpdateRequest $request): JsonResponse
    {
        $reminder = $this->getReminderByTaskTask->run($task);
        $dto = ReminderUpdateData::from($request->validated());

        $reminder = $this->handle($reminder, $dto);

        return fractal($reminder, new ReminderTransformer())
            ->withResourceName('reminders')
            ->addMeta(['message' => 'Reminder successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
