<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\API\Transformers;

use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class ArtistTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'tags'
    ];

    public function transform(Artist $artist): array
    {
        return [
            'id' => $artist->id,
            'name' => $artist->name,
            'content' => $artist->content,
            'image' => $artist->full_image,
            'created_at' => $artist->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeTags(Artist $artist): Collection
    {
        return $this->collection($artist->tags, new TagTransformer(), 'tags');
    }
}
