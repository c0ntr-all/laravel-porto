<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\Actions;

use App\Containers\TaskManagerSection\Reminder\Models\ReminderOccurrence;
use App\Containers\TaskManagerSection\Reminder\Tasks\CompleteReminderTask;
use App\Containers\TaskManagerSection\Reminder\Tasks\GetReminderByTaskTask;
use App\Containers\TaskManagerSection\Reminder\UI\API\Requests\ReminderOccurrenceCreateRequest;
use App\Containers\TaskManagerSection\Reminder\UI\API\Transformers\ReminderOccurrenceTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class CreateReminderOccurrenceAction extends BaseAction
{
    public function __construct(
        private readonly GetReminderByTaskTask $getReminderByTaskTask,
        private readonly CompleteReminderTask $completeReminderTask
    ) {
    }

    public function handle(Task $task, ?Carbon $completedAt = null): ReminderOccurrence
    {
        $reminder = $this->getReminderByTaskTask->run($task);
        $result = $this->completeReminderTask->run($reminder, $completedAt);

        $occurrence = $result['occurrence'];
        $occurrence->setRelation('reminder', $result['reminder']);

        return $occurrence;
    }

    public function asController(Task $task, ReminderOccurrenceCreateRequest $request): JsonResponse
    {
        $completedAt = isset($request->validated()['completed_at'])
            ? Carbon::createFromFormat('Y-m-d H:i:s', $request->validated()['completed_at'])
            : null;

        $occurrence = $this->handle($task, $completedAt);

        return fractal($occurrence, new ReminderOccurrenceTransformer())
            ->withResourceName('reminder-occurrences')
            ->parseIncludes(['reminder'])
            ->addMeta(['message' => 'Reminder occurrence successfully created!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
