<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\API\Transformers;

use App\Containers\GallerySection\Video\Models\Video;
use League\Fractal\TransformerAbstract;

/**
 * Transformer for Video
 */
class VideoTransformer extends TransformerAbstract
{
    public function transform(Video $video): array
    {
        return [
            'id' => $video->id,
            'source' => $video->source,
            'width' => $video->width,
            'height' => $video->height,
            'original_path' => $video->base_path,
            'list_thumb_path' => $video->list_thumb_path,
            'description' => $video->description,
            'created_at' => $video->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
