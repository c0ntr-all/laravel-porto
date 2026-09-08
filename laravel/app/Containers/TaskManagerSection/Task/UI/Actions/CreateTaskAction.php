<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\AppSection\Attachment\Tasks\CreateAttachmentsTask;
use App\Containers\TaskManagerSection\Task\Data\DTO\TaskCreateData;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\CreateTaskTask;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\CreateRequest;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Containers\TaskManagerSection\TaskTemplate\Tasks\ApplyTaskTemplateTask;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreateTaskAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::TM_TASK;

    public function __construct(
        private readonly CreateTaskTask $createTaskTask,
        private readonly ApplyTaskTemplateTask $applyTaskTemplateTask,
        private readonly CreateAttachmentsTask $createAttachmentsTask,
    )
    {
        parent::__construct();
    }

    public function handle(TaskCreateData $dto, ?TaskTemplate $template = null): Task
    {
        return DB::transaction(function () use ($dto, $template) {
            $createdTask = $this->createTaskTask->run($dto);

            if ($template !== null) {
                $createdTask = $this->applyTaskTemplateTask->run($createdTask, $template);
            }

            if (!empty($dto->attachments)) {
                $this->createAttachmentsTask->run(
                    $createdTask,
                    $dto->user_id,
                    ContainerAliasEnum::TM_TASK->value,
                    $dto->attachments
                );
            }

            $this->recordUseCase($createdTask);

            return $createdTask;
        });
    }

    /**
     * @throws \Exception
     */
    public function asController(CreateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $templateId = $validated['task_template_id'] ?? null;
        unset($validated['task_template_id']);

        $template = null;
        if ($templateId !== null) {
            $template = TaskTemplate::with(['checklists.items'])->findOrFail($templateId);
            $validated['title'] = $validated['title'] ?? $template->title;
            $validated['content'] = array_key_exists('content', $validated)
                ? $validated['content']
                : $template->content;
        }

        $dto = TaskCreateData::from($validated);
        $dto->user_id = auth()->user()->id;

        $task = $this->handle($dto, $template);

        $fractal = fractal($task, new TaskTransformer())
            ->withResourceName('tasks')
            ->parseIncludes(['attachments'])
            ->addMeta(['message' => 'New task successfully created!']);

        if ($template !== null) {
            $fractal->parseIncludes(['checklists.checklistItems', 'attachments']);
        }

        return $fractal->respond(201, [], JSON_PRETTY_PRINT);
    }
}
