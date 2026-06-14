<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UpdateRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'task_list_id' => 'sometimes|exists:App\Containers\TaskManagerSection\TaskList\Models\TaskList,id',
            'title' => 'sometimes|string|max:70',
            'content' => 'sometimes|max:3000',
            'is_finished' => 'sometimes|boolean',
            'is_declined' => 'sometimes|boolean',
        ];
    }
}
