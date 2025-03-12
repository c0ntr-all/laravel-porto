<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Tasks;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistUpdateData;
use App\Containers\TaskManagerSection\Checklist\Data\Repositories\ChecklistRepository;
use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateChecklistTask extends ParentTask
{
    public function __construct(
        private readonly ChecklistRepository $checklistRepository
    )
    {
    }

    /**
     * @param Checklist $checklist
     * @param ChecklistUpdateData $dto
     * @return Checklist
     */
    public function run(Checklist $checklist, ChecklistUpdateData $dto): Checklist
    {
        return $this->checklistRepository->update($checklist, $dto);
    }
}
