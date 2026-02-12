<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Tasks;

use App\Containers\TaskManagerSection\Checklist\Data\Repositories\ChecklistItemRepository;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteChecklistItemTask extends ParentTask
{
    public function __construct(
        private readonly ChecklistItemRepository $checklistItemRepository
    )
    {
    }

    /**
     * @param ChecklistItem $checklistItem
     * @return bool
     */
    public function run(ChecklistItem $checklistItem): bool
    {
        return $this->checklistItemRepository->delete($checklistItem);
    }
}
