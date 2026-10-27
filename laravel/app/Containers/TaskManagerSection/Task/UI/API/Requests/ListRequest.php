<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ListRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'task_list_id' => 'sometimes|nullable|integer|exists:App\Containers\TaskManagerSection\TaskList\Models\TaskList,id',
            'parent_id' => 'sometimes|nullable|integer|exists:App\Containers\TaskManagerSection\Task\Models\Task,id',
            'unlisted' => 'sometimes|boolean',
            'is_favorite' => 'sometimes|boolean',
        ];
    }
}
