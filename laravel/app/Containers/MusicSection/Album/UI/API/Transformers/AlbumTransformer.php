<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\API\Transformers;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistInAlbumTransformer;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagTransformer;
use App\Containers\MusicSection\Track\UI\API\Transformers\TrackTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

/**
 * Transformer for Album in album page
 */
class AlbumTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'artists', 'tracks', 'tags', 'versions'
    ];

    public function transform(Album $album): array
    {
        return [
            'id' => $album->id,
            'name' => $album->name,
            'date' => $album->date->format('Y-m-d'),
            'description' => $album->description,
            'image' => $album->full_image,
            'created_at' => $album->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeArtists(Album $album): Collection
    {
        return $this->collection($album->artists, new ArtistInAlbumTransformer(), 'artists');
    }

    public function includeTracks(Album $album): Collection
    {
        return $this->collection($album->tracks, new TrackTransformer(), 'tracks');
    }

    public function includeTags(Album $album): Collection
    {
        return $this->collection($album->tags, new TagTransformer(), 'tags');
    }

    public function includeVersions(Album $album): Collection
    {
        return $this->collection($album->versions, new VersionTransformer(), 'versions')
                    ->setMeta(['count' => $album->versions->count()]);
    }
}
