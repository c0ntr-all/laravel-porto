<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\API\Transformers;

use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistInAlbumTransformer;
use App\Containers\MusicSection\Track\Models\Track;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class TrackInPlaylistTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'artists',
    ];

    public function transform(Track $track): array
    {
        return [
            'id' => $track->id,
            'name' => $track->name,
            'image' => $track->full_image,
        ];
    }

    public function includeArtists(Track $track): Collection
    {
        return $this->collection($track->artists, new ArtistInAlbumTransformer(), 'artists');
    }
}
