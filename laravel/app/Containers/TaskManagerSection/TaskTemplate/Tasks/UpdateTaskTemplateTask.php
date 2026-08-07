<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\Tasks;

use App\Containers\TaskManagerSection\TaskTemplate\Data\DTO\TaskTemplateUpdateData;
use App\Containers\TaskManagerSection\TaskTemplate\Data\Repositories\TaskTemplateRepository;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateTaskTemplateTask extends ParentTask
{
    public function __construct(
        private readonly TaskTemplateRepository $taskTemplateRepository
    ) {
    }

    public function run(TaskTemplate $taskTemplate, TaskTemplateUpdateData $dto): TaskTemplate
    {
        return $this->taskTemplateRepository->update($taskTemplate, $dto);
    }
}
