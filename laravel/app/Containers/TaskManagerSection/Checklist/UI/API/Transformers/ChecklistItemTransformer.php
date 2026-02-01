<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\API\Transformers;

use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use League\Fractal\TransformerAbstract;

class ChecklistItemTransformer extends TransformerAbstract
{
    public function transform(ChecklistItem $checklistItem): array
    {
        return [
            'id' => $checklistItem->id,
            'title' => $checklistItem->title,
            'is_declined' => $checklistItem->is_declined,
            'decline_reason' => $checklistItem->decline_reason,
            'finished_at' => $checklistItem->finished_at?->format('Y-m-d H:i:s'),
            'created_at' => $checklistItem->created_at->format('Y-m-d H:i:s')
        ];
    }
}
