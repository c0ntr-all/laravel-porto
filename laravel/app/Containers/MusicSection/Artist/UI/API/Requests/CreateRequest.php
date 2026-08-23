<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\API\Requests;

use App\Ship\Parents\Requests\AdminRequest;

class CreateRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'sometimes|string|nullable',
            'country_id' => 'sometimes|nullable|integer|exists:countries,id',
            'path' => 'sometimes|string|nullable|max:255',
            'tags' => 'sometimes|array',
            'tags.*' => 'integer|exists:music_tags,id',
            'image_file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:8192|nullable',
        ];
    }
}
