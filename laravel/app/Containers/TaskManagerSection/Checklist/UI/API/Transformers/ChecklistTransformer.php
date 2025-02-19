<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\API\Transformers;

use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class ChecklistTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'checklistItems'
    ];

    public function transform(Checklist $checklist): array
    {
        return [
            'id' => $checklist->id,
            'title' => $checklist->title,
            'created_at' => $checklist->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $checklist->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeChecklistItems(Checklist $checklist): Collection
    {
        return $this->collection($checklist->checklistItems, new ChecklistItemTransformer(), 'checklist_items')
                    ->setMeta(['count' => $checklist->checklistItems->count()]);
    }
}
