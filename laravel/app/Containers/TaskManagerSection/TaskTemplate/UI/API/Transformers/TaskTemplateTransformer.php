<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\UI\API\Transformers;

use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class TaskTemplateTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'checklists',
    ];

    public function transform(TaskTemplate $taskTemplate): array
    {
        return [
            'id' => $taskTemplate->id,
            'title' => $taskTemplate->title,
            'content' => $taskTemplate->content,
            'checklists_count' => $taskTemplate->relationLoaded('checklists')
                ? $taskTemplate->checklists->count()
                : $taskTemplate->checklists()->count(),
            'created_at' => $taskTemplate->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $taskTemplate->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeChecklists(TaskTemplate $taskTemplate): Collection
    {
        return $this->collection($taskTemplate->checklists, new TaskTemplateChecklistTransformer(), 'checklists')
                    ->setMeta(['count' => $taskTemplate->checklists->count()]);
    }
}
