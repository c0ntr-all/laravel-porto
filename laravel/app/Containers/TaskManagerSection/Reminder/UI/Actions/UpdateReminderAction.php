<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\Actions;

use App\Containers\TaskManagerSection\Reminder\Data\DTO\ReminderUpdateData;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Tasks\UpdateReminderTask;
use App\Containers\TaskManagerSection\Reminder\UI\API\Requests\ReminderUpdateRequest;
use App\Containers\TaskManagerSection\Reminder\UI\API\Transformers\ReminderTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateReminderAction
{
    use AsAction;

    public function __construct(
        private readonly UpdateReminderTask $updateReminderTask
    )
    {
    }

    public function handle(Reminder $checklist, ReminderUpdateData $dto): Reminder
    {
        return $this->updateReminderTask->run($checklist, $dto);
    }

    public function asController(Task $task, Reminder $checklist, ReminderUpdateRequest $request): JsonResponse
    {
        $dto = ReminderUpdateData::from($request->validated());

        $checklist = $this->handle($checklist, $dto);

        return fractal($checklist, new ReminderTransformer())
            ->withResourceName('reminder')
            ->addMeta(['message' => 'Reminder successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
