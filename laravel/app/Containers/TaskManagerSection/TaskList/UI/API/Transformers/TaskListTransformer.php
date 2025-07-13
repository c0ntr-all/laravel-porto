<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\UI\API\Transformers;

use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class TaskListTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'tasks'
    ];

    public function transform(TaskList $taskList): array
    {
        return [
            'id' => $taskList->id,
            'title' => $taskList->title,
            'created_at' => $taskList->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeTasks(TaskList $taskList): Collection
    {
        return $this->collection($taskList->tasks, new TaskTransformer(), 'tasks')
                    ->setMeta(['count' => $taskList->tasks->count()]);
    }
}
