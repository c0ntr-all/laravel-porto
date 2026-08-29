<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:music_tags|max:50',
            'slug' => 'sometimes|nullable|string|max:50|unique:music_tags,slug',
            'description' => 'sometimes|nullable|string|max:30000',
            'parent_id' => 'sometimes|nullable|integer|exists:music_tags,id',
            'group_id' => 'required_without:parent_id|nullable|integer|exists:music_tag_groups,id',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
