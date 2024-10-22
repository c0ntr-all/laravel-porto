<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\UI\API\Transformers;

use App\Containers\GallerySection\Media\Models\Media;
use League\Fractal\TransformerAbstract;

/**
 * Transformer for Media in media page
 */
class MediaTransformer extends TransformerAbstract
{
    public function transform(Media $media): array
    {
        return [
            'id' => $media->id,
            'type' => $media->type,
            'path' => $media->path,
            'description' => $media->description,
            'created_at' => $media->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
