<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ListRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1|max:50',
            'filter.text' => 'sometimes|nullable|string|max:200',
            'filter.parent_id' => 'sometimes|nullable|integer',
            'filter.pinned' => 'sometimes|boolean',
            'filter.roots' => 'sometimes|boolean',
            'sort' => 'sometimes|string|in:name,-name,created_at,-created_at,updated_at,-updated_at,pin_position,-pin_position',
        ];
    }
}
