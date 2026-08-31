<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\API\Requests;

use App\Ship\Parents\Requests\AdminRequest;

class UpdateRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|nullable',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'integer|exists:music_tags,id',
            'image_file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:8192|nullable',
        ];
    }
}
