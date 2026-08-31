<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\API\Transformers;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistInAlbumTransformer;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class AlbumInArtistTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'artists', 'tags', 'versions',
    ];

    protected array $defaultIncludes = [
        'versions',
    ];

    public function transform(Album $album): array
    {
        return [
            'id' => $album->id,
            'parent_id' => $album->parent_id,
            'album_type_id' => $album->album_type_id,
            'album_type' => AlbumTypeTransformer::payload($album->albumType),
            'name' => $album->name,
            'edition' => $album->edition,
            'date' => $album->date?->format('Y-m-d'),
            'image' => $album->full_image,
            'versions_count' => $album->versions_count ?? $album->versions->count(),
        ];
    }

    public function includeArtists(Album $album): Collection
    {
        return $this->collection($album->artists, new ArtistInAlbumTransformer(), 'artists');
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
