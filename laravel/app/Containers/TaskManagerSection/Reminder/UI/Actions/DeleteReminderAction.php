<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\Actions;

use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Tasks\DeleteReminderTask;
use App\Containers\TaskManagerSection\Reminder\Tasks\GetReminderByTaskTask;
use App\Containers\TaskManagerSection\Reminder\UI\API\Requests\ReminderDeleteRequest;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeleteReminderAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_REMINDER;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;

    public function __construct(
        private readonly GetReminderByTaskTask $getReminderByTaskTask,
        private readonly DeleteReminderTask $deleteReminderTask
    ) {
        parent::__construct();
    }

    public function handle(Reminder $reminder): bool
    {
        return DB::transaction(function () use ($reminder): bool {
            $this->recordUseCase($reminder);

            return $this->deleteReminderTask->run($reminder);
        });
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
