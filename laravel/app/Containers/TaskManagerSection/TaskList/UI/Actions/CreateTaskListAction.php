<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\UI\Actions;

use App\Containers\TaskManagerSection\TaskList\Data\DTO\TaskListCreateData;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Containers\TaskManagerSection\TaskList\Tasks\CreateTaskListTask;
use App\Containers\TaskManagerSection\TaskList\UI\API\Requests\CreateRequest;
use App\Containers\TaskManagerSection\TaskList\UI\API\Transformers\TaskListTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class CreateTaskListAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK_LIST;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly CreateTaskListTask $createTaskListTask
    ) {
        parent::__construct();
    }

    public function handle(TaskListCreateData $dto): TaskList
    {
        $taskList = $this->createTaskListTask->run($dto);

        $this->recordUseCase($taskList);

        return $taskList;
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = TaskListCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $taskList = $this->handle($dto);

        return fractal($taskList, new TaskListTransformer())
            ->withResourceName('task-lists')
            ->addMeta(['message' => 'New task list successfully created!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
