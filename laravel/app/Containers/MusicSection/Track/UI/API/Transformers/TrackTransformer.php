<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\API\Transformers;

use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumDiscTransformer;
use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumInTrackTransformer;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistInAlbumTransformer;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagTransformer;
use App\Containers\MusicSection\Track\Models\Track;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class TrackTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'tags', 'artists', 'album', 'disc',
    ];

    public function transform(Track $track): array
    {
        return [
            'id' => $track->id,
            'name' => $track->name,
            'credits' => $track->credits,
            'number' => $track->number,
            'cd' => $track->cd,
            'disc_id' => $track->disc_id,
            'image' => $track->full_image,
            'duration' => $track->duration,
            'rate' => $track->rate->first()?->rate ?? 0,
        ];
    }

    public function includeTags(Track $track): Collection
    {
        return $this->collection($track->tags, new TagTransformer(), 'tags');
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

    public function includeDisc(Track $track): Item|NullResource
    {
        if (!$track->disc) {
            return $this->null();
        }

        return $this->item($track->disc, new AlbumDiscTransformer(), 'discs');
    }
}
