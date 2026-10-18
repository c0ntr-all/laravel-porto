<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\API\Transformers;

use App\Containers\AppSection\Attachment\UI\API\Transformers\AttachmentTransformer;
use App\Containers\AppSection\Comment\UI\API\Transformers\CommentTransformer;
use App\Containers\AppSection\CustomField\UI\API\Transformers\CustomFieldTransformer;
use App\Containers\TaskManagerSection\Checklist\UI\API\Transformers\ChecklistItemTransformer;
use App\Containers\TaskManagerSection\Checklist\UI\API\Transformers\ChecklistTransformer;
use App\Containers\TaskManagerSection\Reminder\UI\API\Transformers\ReminderTransformer;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\TaskList\UI\API\Transformers\TaskListTransformer;
use App\Containers\TaskManagerSection\TaskProgress\UI\API\Transformers\TaskProgressTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class TaskTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'taskList',
        'parent',
        'subtasks',
        'sourceChecklistItem',
        'comments',
        'checklists',
        'progress',
        'reminder',
        'attachments',
        'customFields',
    ];

    public function transform(Task $task): array
    {
        return [
            'id' => (string) $task->id,
            'uuid' => (string) $task->uuid,
            'task_list_id' => $task->task_list_id !== null ? (string) $task->task_list_id : null,
            'parent_id' => $task->parent_id !== null ? (string) $task->parent_id : null,
            'title' => $task->title,
            'content' => $task->content,
            'finished_at' => $task->finished_at?->format('Y-m-d H:i:s'),
            'is_declined' => (bool) $task->is_declined,
            'reminders_count' => $task->relationLoaded('reminder')
                ? ($task->reminder ? 1 : 0)
                : $task->reminder()->count(),
            'checklists_count' => $task->relationLoaded('checklists')
                ? $task->checklists->count()
                : $task->checklists()->count(),
            'subtasks_count' => $task->relationLoaded('subtasks')
                ? $task->subtasks->count()
                : $task->subtasks()->count(),
            'progresses_count' => $task->relationLoaded('progress')
                ? $task->progress->count()
                : $task->progress()->count(),
            'created_at' => $task->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $task->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeTaskList(Task $task): ?Item
    {
        if (!$task->relationLoaded('taskList') || $task->taskList === null) {
            if ($task->task_list_id === null) {
                return null;
            }
            $task->loadMissing('taskList');
        }

        if ($task->taskList === null) {
            return null;
        }

        return $this->item($task->taskList, new TaskListTransformer(), 'task-lists');
    }

    public function includeParent(Task $task): Item|NullResource
    {
        if (!$task->relationLoaded('parent') || $task->parent === null) {
            if ($task->parent_id === null) {
                return $this->null();
            }
            $task->loadMissing('parent');
        }

        if ($task->parent === null) {
            return $this->null();
        }

        return $this->item($task->parent, new self(), 'tasks');
    }

    public function includeSubtasks(Task $task): Collection
    {
        $subtasks = $task->relationLoaded('subtasks')
            ? $task->subtasks
            : $task->subtasks()->get();

        return $this->collection($subtasks, new self(), 'tasks')
                    ->setMeta(['count' => $subtasks->count()]);
    }

    public function includeSourceChecklistItem(Task $task): Item|NullResource
    {
        $item = $task->relationLoaded('sourceChecklistItem')
            ? $task->sourceChecklistItem
            : $task->sourceChecklistItem()->first();

        if ($item === null) {
            return $this->null();
        }

        return $this->item($item, new ChecklistItemTransformer(), 'checklist_items');
    }

    public function includeComments(Task $task): Collection
    {
        $comments = $task->relationLoaded('comments') ? $task->comments : $task->comments()->get();

        return $this->collection($comments, new CommentTransformer(), 'comments')
                    ->setMeta(['count' => $comments->count()]);
    }

    public function includeChecklists(Task $task): Collection
    {
        $checklists = $task->relationLoaded('checklists') ? $task->checklists : $task->checklists()->get();

        return $this->collection($checklists, new ChecklistTransformer(), 'checklists')
                    ->setMeta(['count' => $checklists->count()]);
    }

    public function includeProgress(Task $task): Collection
    {
        $progress = $task->relationLoaded('progress')
            ? $task->progress
            : $task->progress()->get();

        $sorted = $progress->sortBy('finished_at')->values();

        return $this->collection($sorted, new TaskProgressTransformer(), 'progress')
                    ->setMeta(['count' => $sorted->count()]);
    }

    public function includeReminder(Task $task): ?Item
    {
        $reminder = $task->relationLoaded('reminder')
            ? $task->reminder
            : $task->reminder()->first();

        if ($reminder === null) {
            return null;
        }

        return $this->item($reminder, new ReminderTransformer(), 'reminders');
    }

    public function includeAttachments(Task $task): Collection
    {
        $attachments = $task->relationLoaded('attachments')
            ? $task->attachments
            : $task->attachments()->with('fileable')->get();

        return $this->collection($attachments, new AttachmentTransformer(), 'attachments')
            ->setMeta(['count' => $attachments->count()]);
    }

    public function includeCustomFields(Task $task): Collection
    {
        $customFields = $task->relationLoaded('customFields')
            ? $task->customFields
            : $task->customFields()->get();

        return $this->collection($customFields, new CustomFieldTransformer(), 'custom_fields')
            ->setMeta(['count' => $customFields->count()]);
    }
}
