<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\API\Transformers;

use App\Containers\MusicSection\Album\Models\Album;
use League\Fractal\TransformerAbstract;

class VersionTransformer extends TransformerAbstract
{
    public function transform(Album $album): array
    {
        return [
            'id' => $album->id,
            'parent_id' => $album->parent_id,
            'name' => $album->name,
            'date' => $album->date->format('Y-m-d'),
            'content' => $album->content,
            'image' => $album->full_image,
            'created_at' => $album->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
