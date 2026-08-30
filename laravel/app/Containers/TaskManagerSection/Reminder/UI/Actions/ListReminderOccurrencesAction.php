<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\Actions;

use App\Containers\TaskManagerSection\Reminder\Tasks\GetReminderByTaskTask;
use App\Containers\TaskManagerSection\Reminder\Tasks\ListReminderOccurrencesTask;
use App\Containers\TaskManagerSection\Reminder\UI\API\Requests\ReminderOccurrenceListRequest;
use App\Containers\TaskManagerSection\Reminder\UI\API\Transformers\ReminderOccurrenceTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListReminderOccurrencesAction extends BaseAction
{
    public function __construct(
        private readonly GetReminderByTaskTask $getReminderByTaskTask,
        private readonly ListReminderOccurrencesTask $listReminderOccurrencesTask
    ) {
    }

    public function handle(Task $task): Collection
    {
        $reminder = $this->getReminderByTaskTask->run($task);

        return $this->listReminderOccurrencesTask->run($reminder);
    }

    public function asController(Task $task, ReminderOccurrenceListRequest $request): JsonResponse
    {
        $occurrences = $this->handle($task);

        return fractal($occurrences, new ReminderOccurrenceTransformer())
            ->withResourceName('reminder-occurrences')
            ->addMeta(['count' => $occurrences->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
