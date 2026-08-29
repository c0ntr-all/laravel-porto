<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class TagGroupCreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
            'slug' => 'sometimes|nullable|string|max:50|unique:music_tag_groups,slug',
            'description' => 'sometimes|nullable|string|max:30000',
            'is_system' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'display_order' => 'sometimes|integer|min:0',
        ];
    }
}
