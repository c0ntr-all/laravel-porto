<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Tasks;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistCreateData;
use App\Containers\TaskManagerSection\Checklist\Data\Repositories\ChecklistRepository;
use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateChecklistTask extends ParentTask
{
    public function __construct(
        private readonly ChecklistRepository $checklistRepository
    )
    {
    }

    /**
     * @param ChecklistCreateData $dto
     * @return Checklist
     */
    public function run(ChecklistCreateData $dto): Checklist
    {
        return $this->checklistRepository->create($dto);
    }
}
