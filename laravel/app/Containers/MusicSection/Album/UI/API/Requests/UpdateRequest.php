<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\API\Requests;

use App\Ship\Parents\Requests\AdminRequest;

class UpdateRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeTags();
    }

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
            'discs' => 'sometimes|array',
            'discs.*.id' => 'required|integer|exists:music_album_discs,id',
            'discs.*.name' => 'required|string|max:255',
            'image_file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:8192|nullable',
        ];
    }

    private function normalizeTags(): void
    {
        if (!$this->exists('tags')) {
            return;
        }

        $tags = $this->input('tags');

        if (!is_array($tags)) {
            $this->merge(['tags' => []]);

            return;
        }

        $this->merge([
            'tags' => array_values(array_filter(
                $tags,
                static fn (mixed $id): bool => $id !== null && $id !== '',
            )),
        ]);
    }
}
