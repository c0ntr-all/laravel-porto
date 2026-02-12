<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Data\Repositories;

use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;

class ChecklistItemRepository
{
    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data): ChecklistItem
    {
        return ChecklistItem::create($data);
    }

    /**
     * @param ChecklistItem $checklistItem
     * @param array $data
     * @return ChecklistItem
     */
    public function update(ChecklistItem $checklistItem, array $data): ChecklistItem
    {
        $checklistItem->update($data);

        return $checklistItem;
    }

    /**
     * @param ChecklistItem $checklistItem
     * @return bool
     */
    public function delete(ChecklistItem $checklistItem): bool
    {
        return $checklistItem->delete();
    }
}
