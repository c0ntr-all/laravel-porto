<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\DeleteTaskTask;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\DeleteRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteTaskAction extends BaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK;

    public function __construct(
        private readonly DeleteTaskTask $deleteTaskTask
    )
    {
        parent::__construct();
    }

    public function handle(Task $task): bool
    {
        return $this->deleteTaskTask->run($task);
    }

    public function asController(Task $task, DeleteRequest $request): JsonResponse
    {
        $this->handle($task);

        return response()->json(
            ['meta' => [
                'message' => 'Task successfully deleted!'
            ]]
        );
    }
}
