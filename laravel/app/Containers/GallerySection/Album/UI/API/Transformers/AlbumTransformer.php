<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\API\Transformers;

use App\Containers\AppSection\User\UI\Transformer\UserTransformer;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Containers\GallerySection\Video\UI\API\Transformers\VideoTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class AlbumTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'images',
        'videos',
        'user',
    ];

    public function transform(Album $album): array
    {
        return [
            'id' => (string) $album->id,
            'name' => $album->name,
            'description' => $album->description,
            'image' => $album->full_image,
            'system_code' => $album->system_code,
            'images_count' => $album->images_count
                ?? ($album->relationLoaded('images') ? $album->images->count() : null),
            'videos_count' => $album->videos_count
                ?? ($album->relationLoaded('videos') ? $album->videos->count() : null),
            'created_at' => $album->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $album->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeImages(Album $album): Collection
    {
        $images = $album->relationLoaded('images') ? $album->images : $album->images()->get();

        return $this->collection($images, new ImageTransformer(), 'images')
                    ->setMeta(['count' => $images->count()]);
    }

    public function includeVideos(Album $album): Collection
    {
        $videos = $album->relationLoaded('videos') ? $album->videos : $album->videos()->get();

        return $this->collection($videos, new VideoTransformer(), 'videos')
                    ->setMeta(['count' => $videos->count()]);
    }

    public function includeUser(Album $album): ?Item
    {
        $user = $album->relationLoaded('user') ? $album->user : $album->user()->first();

        if ($user === null) {
            return null;
        }

        return $this->item($user, new UserTransformer(), 'users');
    }
}
