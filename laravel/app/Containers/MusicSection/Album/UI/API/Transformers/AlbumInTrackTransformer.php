<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\API\Transformers;

use App\Containers\MusicSection\Album\Models\Album;
use League\Fractal\TransformerAbstract;

class AlbumInTrackTransformer extends TransformerAbstract
{
    public function transform(Album $album): array
    {
        return [
            'id' => $album->id,
            'album_type_id' => $album->album_type_id,
            'album_type' => AlbumTypeTransformer::payload($album->albumType),
            'name' => $album->name,
            'edition' => $album->edition,
            'date' => $album->date?->format('Y-m-d'),
            'image' => $album->full_image,
        ];
    }
}
