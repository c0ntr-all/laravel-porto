<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $tag = $this->route('tag');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('music_tags', 'name')->ignore($tag),
            ],
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('music_tags', 'slug')->ignore($tag),
            ],
            'description' => 'sometimes|string|nullable|max:30000',
            'parent_id' => 'sometimes|nullable|integer|exists:music_tags,id',
            'group_id' => 'sometimes|nullable|integer|exists:music_tag_groups,id',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
