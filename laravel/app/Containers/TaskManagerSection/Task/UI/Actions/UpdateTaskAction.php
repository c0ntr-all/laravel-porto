<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\TaskManagerSection\Task\Data\DTO\TaskUpdateData;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\UpdateTaskTask;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\UpdateRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class UpdateTaskAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly UpdateTaskTask $updateTaskTask
    )
    {
        parent::__construct();
    }

    public function handle(Task $task, TaskUpdateData $dto): Task
    {
        return $this->updateTaskTask->run($task, $dto);
    }

    public function asController(Task $task, UpdateRequest $request): JsonResponse
    {
        $dto = TaskUpdateData::from($request->validated());
        if (isset($request->is_finished)) {
            $dto->finished_at = $request->is_finished === true ? Carbon::now() : null;
        }

        $task = $this->handle($task, $dto);

        return fractal($task, new TaskTransformer())
            ->withResourceName('tasks')
            ->addMeta(['message' => 'Task successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
