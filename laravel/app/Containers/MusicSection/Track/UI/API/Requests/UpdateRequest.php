<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\API\Requests;

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
            'album_id' => 'sometimes|integer|exists:music_albums,id',
            'name' => 'sometimes|string|max:255',
            'number' => 'sometimes|nullable|integer|min:1',
            'cd' => 'sometimes|nullable|string|max:10',
            'disc_id' => 'sometimes|nullable|integer|exists:music_album_discs,id',
            'credits' => 'sometimes|nullable|string',
            'duration' => 'sometimes|nullable|string|max:20',
            'bitrate' => 'sometimes|nullable|integer',
            'link' => 'sometimes|nullable|string|max:255',
            'lyrics' => 'sometimes|nullable|string',
            'path' => 'sometimes|nullable|string|max:255',
            'artist_ids' => 'sometimes|array',
            'artist_ids.*' => 'integer|exists:music_artists,id',
            'featured_artist_ids' => 'sometimes|array',
            'featured_artist_ids.*' => 'integer|exists:music_artists,id',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'integer|exists:music_tags,id',
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
