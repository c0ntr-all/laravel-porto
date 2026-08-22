<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:50',
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => 'sometimes|string|max:1000',
            'icon' => 'sometimes|string|max:50',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|max:50',
            'date_from' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'date_to' => 'sometimes|nullable|date_format:Y-m-d H:i|after_or_equal:date_from',
            'text' => 'sometimes|nullable|string|max:255',
        ];
    }
}
