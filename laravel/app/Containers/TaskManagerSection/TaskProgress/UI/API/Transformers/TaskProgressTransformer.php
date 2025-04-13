<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\UI\API\Transformers;

use App\Containers\TaskManagerSection\TaskProgress\Models\TaskProgress;
use League\Fractal\TransformerAbstract;

class TaskProgressTransformer extends TransformerAbstract
{
    public function transform(TaskProgress $taskProgress): array
    {
        return [
            'id' => $taskProgress->id,
            'task_id' => $taskProgress->task_id,
            'title' => $taskProgress->title,
            'content' => $taskProgress->content,
            'is_final' => $taskProgress->is_final,
            'finished_at' => $taskProgress->finished_at->format('Y-m-d H:i:s'),
            'created_at' => $taskProgress->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $taskProgress->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
