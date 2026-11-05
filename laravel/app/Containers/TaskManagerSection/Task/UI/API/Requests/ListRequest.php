<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\API\Requests;

use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Ship\Parents\Requests\AuthenticatedRequest;

class ListRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'task_list_id' => ['sometimes', 'nullable', 'integer', $this->existsForCurrentUser(TaskList::class)],
            'parent_id' => ['sometimes', 'nullable', 'integer', $this->existsForCurrentUser(Task::class)],
            'unlisted' => 'sometimes|boolean',
            'is_favorite' => 'sometimes|boolean',
        ];
    }
}
