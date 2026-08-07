<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\Tasks;

use App\Containers\TaskManagerSection\TaskTemplate\Data\DTO\TaskTemplateCreateData;
use App\Containers\TaskManagerSection\TaskTemplate\Data\Repositories\TaskTemplateRepository;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateTaskTemplateTask extends ParentTask
{
    public function __construct(
        private readonly TaskTemplateRepository $taskTemplateRepository
    ) {
    }

    public function run(TaskTemplateCreateData $dto): TaskTemplate
    {
        return $this->taskTemplateRepository->create($dto);
    }
}
