<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\TaskManagerSection\Task\Data\DTO\TaskCreateData;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\CreateTaskTask;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\CreateRequest;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Helpers\Correlation;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreateTaskAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK;

    public function __construct(
        private readonly CreateTaskTask $createTaskTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask
    )
    {
        parent::__construct();
    }

    public function handle(TaskCreateData $dto): Task
    {
        $createdTask = $this->createTaskTask->run($dto);

        // После успешного коммита формируем user_log
        DB::afterCommit(function () use ($createdTask) {
            $this->createActivityUseCaseTask->run($createdTask, $this->eventTypesEnum->value);
        });

        return $createdTask;
    }

    /**
     * @throws \Exception
     */
    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = TaskCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $task = $this->handle($dto);

        return fractal($task, new TaskTransformer())
            ->withResourceName('tasks')
            ->addMeta(['message' => 'New task successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
