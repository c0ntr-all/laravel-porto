<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\UI\API\Transformers;

use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplateChecklist;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class TaskTemplateChecklistTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'items',
    ];

    protected array $defaultIncludes = [
        'items',
    ];

    public function transform(TaskTemplateChecklist $checklist): array
    {
        return [
            'id' => $checklist->id,
            'title' => $checklist->title,
            'position' => $checklist->position,
            'created_at' => $checklist->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeItems(TaskTemplateChecklist $checklist): Collection
    {
        return $this->collection($checklist->items, new TaskTemplateChecklistItemTransformer(), 'items')
                    ->setMeta(['count' => $checklist->items->count()]);
    }
}
