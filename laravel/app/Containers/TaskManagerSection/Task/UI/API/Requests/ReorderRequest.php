<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\API\Requests;

use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Parents\Requests\AuthenticatedRequest;

class ReorderRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => ['integer', 'distinct', $this->existsForCurrentUser(Task::class)],
        ];
    }
}
