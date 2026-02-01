<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Tasks;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistItemUpdateData;
use App\Containers\TaskManagerSection\Checklist\Data\Repositories\ChecklistItemRepository;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateChecklistItemTask extends ParentTask
{
    public function __construct(
        private readonly ChecklistItemRepository $checklistItemRepository
    )
    {
    }

    /**
     * @param ChecklistItem $checklistItem
     * @param ChecklistItemUpdateData $dto
     * @return ChecklistItem
     */
    public function run(ChecklistItem $checklistItem, ChecklistItemUpdateData $dto): ChecklistItem
    {
        return $this->checklistItemRepository->update($checklistItem, $dto->toArray());
    }
}
