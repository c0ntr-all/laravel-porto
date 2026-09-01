<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\API\Requests;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'task_list_id' => 'sometimes|exists:App\Containers\TaskManagerSection\TaskList\Models\TaskList,id',
            'task_template_id' => 'sometimes|exists:App\Containers\TaskManagerSection\TaskTemplate\Models\TaskTemplate,id',
            'title' => 'required_without:task_template_id|string|max:70',
            'content' => 'sometimes|nullable|max:3000',
            'attachments' => 'sometimes|array',
            'attachments.*.type' => [
                'required',
                Rule::in(ContainerAliasEnum::attachmentFileableTypes()),
            ],
            'attachments.*.id' => 'required|uuid',
        ];
    }
}
