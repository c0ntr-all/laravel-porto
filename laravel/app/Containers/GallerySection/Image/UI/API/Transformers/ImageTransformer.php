<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\API\Transformers;

use App\Containers\GallerySection\Album\UI\API\Transformers\AlbumTransformer;
use App\Containers\GallerySection\Image\Models\Image;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class ImageTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'album',
    ];

    public function transform(Image $image): array
    {
        return [
            'id' => (string) $image->id,
            'album_id' => (string) $image->album_id,
            'source' => $image->source,
            'width' => $image->width,
            'height' => $image->height,
            'original_path' => $image->base_path,
            'list_thumb_path' => $image->list_thumb_path,
            'preview_thumb_path' => $image->preview_thumb_path,
            'description' => $image->description,
            'created_at' => $image->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $image->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeAlbum(Image $image): ?Item
    {
        $album = $image->relationLoaded('album') ? $image->album : $image->album()->first();

        if ($album === null) {
            return null;
        }

        return $this->item($album, new AlbumTransformer(), 'albums');
    }
}
