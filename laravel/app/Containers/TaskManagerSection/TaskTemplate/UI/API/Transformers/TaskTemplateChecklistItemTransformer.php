<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\UI\API\Transformers;

use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplateChecklistItem;
use League\Fractal\TransformerAbstract;

class TaskTemplateChecklistItemTransformer extends TransformerAbstract
{
    public function transform(TaskTemplateChecklistItem $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'position' => $item->position,
            'created_at' => $item->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
