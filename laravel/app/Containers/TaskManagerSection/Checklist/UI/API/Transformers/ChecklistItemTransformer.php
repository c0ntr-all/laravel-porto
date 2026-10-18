<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\API\Transformers;

use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class ChecklistItemTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'convertedTask',
    ];

    public function transform(ChecklistItem $checklistItem): array
    {
        return [
            'id' => $checklistItem->id,
            'title' => $checklistItem->title,
            'is_declined' => $checklistItem->is_declined,
            'decline_reason' => $checklistItem->decline_reason,
            'converted_task_id' => $checklistItem->converted_task_id !== null
                ? (string) $checklistItem->converted_task_id
                : null,
            'finished_at' => $checklistItem->finished_at?->format('Y-m-d H:i:s'),
            'created_at' => $checklistItem->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeConvertedTask(ChecklistItem $checklistItem): Item|NullResource
    {
        if (!$checklistItem->relationLoaded('convertedTask') || $checklistItem->convertedTask === null) {
            if ($checklistItem->converted_task_id === null) {
                return $this->null();
            }
            $checklistItem->loadMissing('convertedTask');
        }

        if ($checklistItem->convertedTask === null) {
            return $this->null();
        }

        return $this->item($checklistItem->convertedTask, new TaskTransformer(), 'tasks');
    }
}
