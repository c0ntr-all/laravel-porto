<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Tasks;

use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindTaskByIdForUserTask extends ParentTask
{
    public function run(int $taskId, int $userId): Task
    {
        $task = Task::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->find($taskId);

        if (!$task) {
            throw (new ModelNotFoundException())->setModel(Task::class, [$taskId]);
        }

        return $task;
    }
}
