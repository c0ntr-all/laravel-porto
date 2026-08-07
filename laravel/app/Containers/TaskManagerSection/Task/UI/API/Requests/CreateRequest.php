<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
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
            'task_template_id' => 'sometimes|exists:App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate,id',
            'title' => 'required_without:task_template_id|string|max:70',
            'content' => 'sometimes|nullable|max:3000',
        ];
    }
}
