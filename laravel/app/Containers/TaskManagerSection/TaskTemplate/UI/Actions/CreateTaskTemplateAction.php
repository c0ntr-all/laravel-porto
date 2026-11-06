<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\UI\Actions;

use App\Containers\TaskManagerSection\TaskTemplate\Data\DTO\TaskTemplateCreateData;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Containers\TaskManagerSection\TaskTemplate\Tasks\CreateTaskTemplateTask;
use App\Containers\TaskManagerSection\TaskTemplate\UI\API\Requests\CreateRequest;
use App\Containers\TaskManagerSection\TaskTemplate\UI\API\Transformers\TaskTemplateTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreateTaskTemplateAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK_TEMPLATE;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly CreateTaskTemplateTask $createTaskTemplateTask
    ) {
        parent::__construct();
    }

    public function handle(TaskTemplateCreateData $dto): TaskTemplate
    {
        return DB::transaction(function () use ($dto): TaskTemplate {
            $taskTemplate = $this->createTaskTemplateTask->run($dto);

            $this->recordUseCase($taskTemplate);

            return $taskTemplate;
        });
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = TaskTemplateCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $taskTemplate = $this->handle($dto);

        return fractal($taskTemplate, new TaskTemplateTransformer())
            ->withResourceName('task-templates')
            ->parseIncludes(['checklists'])
            ->addMeta(['message' => 'New task template successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
