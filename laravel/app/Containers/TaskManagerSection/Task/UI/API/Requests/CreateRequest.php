<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\API\Requests;

use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'task_list_id' => ['sometimes', $this->existsForCurrentUser(TaskList::class)],
            'parent_id' => ['sometimes', 'nullable', $this->existsForCurrentUser(Task::class)],
            'task_template_id' => ['sometimes', $this->existsForCurrentUser(TaskTemplate::class)],
            'title' => 'required_without:task_template_id|string|max:70',
            'content' => 'sometimes|nullable|max:3000',
            'is_favorite' => 'sometimes|boolean',
            'priority' => 'sometimes|integer|min:0',
            'attachments' => 'sometimes|array',
            'attachments.*.type' => [
                'required',
                Rule::in(ContainerAliasEnum::attachmentFileableTypes()),
            ],
            'attachments.*.id' => 'required|string|max:36',
        ];
    }
}
