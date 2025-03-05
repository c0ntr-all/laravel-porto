<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Data\Repositories;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistItemCreateData;
use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistItemUpdateData;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;

class ChecklistItemRepository
{
    /**
     * @param ChecklistItemCreateData $dto
     * @return mixed
     */
    public function create(ChecklistItemCreateData $dto): ChecklistItem
    {
        return ChecklistItem::create($dto->toArray());
    }

    /**
     * @param ChecklistItem $checklistItem
     * @param ChecklistItemUpdateData $dto
     * @return ChecklistItem
     */
    public function update(ChecklistItem $checklistItem, ChecklistItemUpdateData $dto): ChecklistItem
    {
        $checklistItem->update($dto->toArray());

        return $checklistItem;
    }
}
