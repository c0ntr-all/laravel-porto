<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\API\Transformers;

use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumInTrackTransformer;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistInAlbumTransformer;
use App\Containers\MusicSection\Track\Models\Track;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class TrackInPlaylistTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'artists',
        'album',
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

    public function includeAlbum(Track $track): Item|NullResource
    {
        if (!$track->album) {
            return $this->null();
        }

        return $this->item($track->album, new AlbumInTrackTransformer(), 'albums');
    }
}
