<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\API\Transformers;

use App\Containers\GallerySection\Image\Models\Image;
use League\Fractal\TransformerAbstract;

/**
 * Transformer for Image
 */
class ImageTransformer extends TransformerAbstract
{
    public function transform(Image $image): array
    {
        return [
            'id' => $image->id,
            'source' => $image->source,
            'original_path' => $image->full_path,
            'list_thumb_path' => url('') . '/storage/' . $image->list_thumb_path,
            'preview_thumb_path' => url('') . '/storage/' . $image->preview_thumb_path,
            'description' => $image->description,
            'created_at' => $image->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
