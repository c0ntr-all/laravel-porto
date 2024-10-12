<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\API\Transformers;

use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistInAlbumTransformer;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagTransformer;
use App\Containers\MusicSection\Track\Models\Track;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class TrackTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'tags', 'artists'
    ];

    public function transform(Track $track): array
    {
        return [
            'id' => $track->id,
            'name' => $track->name,
            'image' => $track->full_image,
            'duration' => $track->duration,
            'rate' => !$track->rate->isEmpty() ? $track->rate[0]['rate'] : 0
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
}
