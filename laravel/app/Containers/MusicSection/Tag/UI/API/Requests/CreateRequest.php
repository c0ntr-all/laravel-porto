<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Requests;

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
            'name' => 'required|string|unique:music_tags|max:50',
            'content' => 'sometimes|string|max:30000',
            'parent_id' => 'sometimes|nullable|integer|exists:music_tags,id',
            'is_base' => 'sometimes|boolean',
        ];
    }
}
