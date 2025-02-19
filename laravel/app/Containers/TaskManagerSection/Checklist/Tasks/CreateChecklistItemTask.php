<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Tasks;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistItemCreateData;
use App\Containers\TaskManagerSection\Checklist\Data\Repositories\ChecklistItemRepository;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateChecklistItemTask extends ParentTask
{
    public function __construct(
        private readonly ChecklistItemRepository $checklistItemRepository
    )
    {
    }

    /**
     * @param ChecklistItemCreateData $dto
     * @return ChecklistItem
     */
    public function run(ChecklistItemCreateData $dto): ChecklistItem
    {
        return $this->checklistItemRepository->create($dto);
    }
}
