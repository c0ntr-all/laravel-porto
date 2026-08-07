<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:70',
            'content' => 'sometimes|nullable|string|max:3000',
            'checklists' => 'sometimes|array',
            'checklists.*.title' => 'required|string|max:255',
            'checklists.*.items' => 'sometimes|array',
            'checklists.*.items.*.title' => 'required|string|max:255',
        ];
    }
}
