<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class TagGroupUpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $group = $this->route('tagGroup');

        return [
            'name' => 'sometimes|string|max:50',
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('music_tag_groups', 'slug')->ignore($group),
            ],
            'description' => 'sometimes|nullable|string|max:30000',
            'is_system' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'display_order' => 'sometimes|integer|min:0',
        ];
    }
}
