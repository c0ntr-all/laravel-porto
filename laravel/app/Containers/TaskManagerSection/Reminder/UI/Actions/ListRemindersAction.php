<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\Actions;

use App\Containers\TaskManagerSection\Reminder\Tasks\ListRemindersTask;
use App\Containers\TaskManagerSection\Reminder\UI\API\Requests\ReminderListRequest;
use App\Containers\TaskManagerSection\Reminder\UI\API\Transformers\ReminderTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListRemindersAction extends BaseAction
{
    public function __construct(
        private readonly ListRemindersTask $listRemindersTask
    ) {
    }

    public function handle(): Collection
    {
        return $this->listRemindersTask->run();
    }

    public function asController(ReminderListRequest $request): JsonResponse
    {
        $reminders = $this->handle();

        return fractal($reminders, new ReminderTransformer())
            ->withResourceName('reminders')
            ->addMeta(['count' => $reminders->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
