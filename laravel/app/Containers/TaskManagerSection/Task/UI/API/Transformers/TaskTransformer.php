<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\API\Transformers;

use App\Containers\AppSection\Comment\UI\API\Transformers\CommentTransformer;
use App\Containers\TaskManagerSection\Checklist\UI\API\Transformers\ChecklistTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class TaskTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'comments',
        'checklists'
    ];

    public function transform(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'content' => $task->content,
            'finished_at' => $task->finished_at?->format('Y-m-d H:i:s'),
            'created_at' => $task->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeComments(Task $task): Collection
    {
        return $this->collection($task->comments, new CommentTransformer(), 'comments')
                    ->setMeta(['count' => $task->comments->count()]);
    }

    public function includeChecklists(Task $task): Collection
    {
        return $this->collection($task->checklists, new ChecklistTransformer(), 'checklists')
                    ->setMeta(['count' => $task->checklists->count()]);
    }
}
