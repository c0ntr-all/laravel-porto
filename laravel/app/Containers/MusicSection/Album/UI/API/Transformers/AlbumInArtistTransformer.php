<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\API\Transformers;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistInAlbumTransformer;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

/**
 * Transformer for Album in album page
 */
class AlbumInArtistTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'artists', 'tags'
    ];

    public function transform(Album $album): array
    {
        return [
            'id' => $album->id,
            'name' => $album->name,
            'date' => $album->date->format('Y-m-d'),
            'image' => $album->full_image,
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
}
