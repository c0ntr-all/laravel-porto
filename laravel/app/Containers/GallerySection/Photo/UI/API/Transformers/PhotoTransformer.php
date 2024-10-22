<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Photo\UI\API\Transformers;

use App\Containers\GallerySection\Photo\Models\Photo;
use League\Fractal\TransformerAbstract;

/**
 * Transformer for Photo in photo page
 */
class PhotoTransformer extends TransformerAbstract
{
    public function transform(Photo $photo): array
    {
        return [
            'id' => $photo->id,
            'image' => $photo->image,
            'description' => $photo->description,
            'created_at' => $photo->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
