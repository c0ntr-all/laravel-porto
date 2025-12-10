<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\API\Transformers;

use App\Containers\AppSection\User\UI\Transformer\UserTransformer;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

/**
 * Transformer for Album in album page
 */
class AlbumTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'images'
    ];

    public function transform(Album $album): array
    {
        return [
            'id' => $album->id,
            'name' => $album->name,
            'description' => $album->description,
            'image' => $album->full_image,
            'created_at' => $album->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeImages(Album $album): Collection
    {
        return $this->collection($album->images, new ImageTransformer(), 'images')
                    ->setMeta(['count' => $album->images->count()]);
    }
}
