<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\UI\API\Transformers;

use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class TaskListTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'tasks',
    ];

    public function transform(TaskList $taskList): array
    {
        return [
            'id' => (string) $taskList->id,
            'title' => $taskList->title,
            'tasks_count' => $taskList->relationLoaded('tasks')
                ? $taskList->tasks->count()
                : $taskList->tasks()->count(),
            'created_at' => $taskList->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $taskList->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeTasks(TaskList $taskList): Collection
    {
        $tasks = $taskList->relationLoaded('tasks')
            ? $taskList->tasks
            : $taskList->tasks()->get();

        return $this->collection($tasks, new TaskTransformer(), 'tasks')
                    ->setMeta(['count' => $tasks->count()]);
    }
}
