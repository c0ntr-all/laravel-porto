<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\API\Transformers;

use App\Containers\MusicSection\Album\Models\AlbumDisc;
use App\Containers\MusicSection\Track\UI\API\Transformers\TrackTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class AlbumDiscTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'tracks',
    ];

    public function transform(AlbumDisc $disc): array
    {
        return [
            'id' => $disc->id,
            'album_id' => $disc->album_id,
            'number' => $disc->number,
            'name' => $disc->name,
            'tracks_count' => $disc->tracks_count ?? $disc->tracks->count(),
        ];
    }

    public function includeTracks(AlbumDisc $disc): Collection
    {
        return $this->collection($disc->tracks, new TrackTransformer(), 'tracks');
    }
}
