<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\API\Transformers;

use App\Containers\AppSection\Comment\UI\API\Transformers\CommentTransformer;
use App\Containers\TaskManagerSection\Checklist\UI\API\Transformers\ChecklistTransformer;
use App\Containers\TaskManagerSection\Reminder\UI\API\Transformers\ReminderTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\TaskProgress\UI\API\Transformers\TaskProgressTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class TaskTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'comments',
        'checklists',
        'progress',
        'reminders'
    ];

    public function transform(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'content' => $task->content,
            'finished_at' => $task->finished_at?->format('Y-m-d H:i:s'),
            'is_declined' => $task->is_declined,
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

    public function includeProgress(Task $task): Collection
    {
        return $this->collection($task->progress, new TaskProgressTransformer(), 'progress')
                    ->setMeta(['count' => $task->progress->count()]);
    }

    public function includeReminders(Task $task): Collection
    {
        return $this->collection($task->reminders, new ReminderTransformer(), 'reminders')
                    ->setMeta(['count' => $task->reminders->count()]);
    }
}
