<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\Actions;

use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\UI\API\Requests\GetRequest;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetTaskAction extends BaseAction
{
    public function handle(Task $task): Task
    {
        return $task->load([
            'comments.user',
            'reminder',
            'checklists.checklistItems',
            'progress',
            'attachments.fileable',
        ]);
    }

    public function asController(Task $task, GetRequest $request): JsonResponse
    {
        $task = $this->handle($task);

        if (str_contains((string) $request->query('include', ''), 'customFields')) {
            $task->load('customFields');
        }

        $fractal = fractal($task, new TaskTransformer())
            ->withResourceName('tasks');

        // Default detail payload; client may override via ?include=
        if (!$request->filled('include')) {
            $fractal->parseIncludes([
                'checklists.checklistItems',
                'progress',
                'reminder',
                'attachments',
            ]);
        }

        return $fractal->respond(200, [], JSON_PRETTY_PRINT);
    }
}
