<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\API\Requests;

use App\Ship\Parents\Requests\AdminRequest;

class UpdateRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|nullable',
            'date' => 'sometimes|nullable|date_format:Y-m-d',
            'album_type_id' => 'sometimes|nullable|integer|exists:music_album_types,id',
            'parent_id' => 'sometimes|nullable|integer|exists:music_albums,id',
            'path' => 'sometimes|string|nullable|max:255',
            'is_date_verified' => 'sometimes|boolean',
            'edition' => 'sometimes|string|nullable|max:255',
            'artist_ids' => 'sometimes|array|min:1',
            'artist_ids.*' => 'integer|exists:music_artists,id',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'integer|exists:music_tags,id',
            'image_file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:8192|nullable',
        ];
    }
}
