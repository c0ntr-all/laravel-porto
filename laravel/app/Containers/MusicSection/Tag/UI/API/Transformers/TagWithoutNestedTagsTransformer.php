<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Transformers;

use App\Containers\MusicSection\Tag\Models\MusicTag;
use League\Fractal\TransformerAbstract;

class TagWithoutNestedTagsTransformer extends TransformerAbstract
{
    public function transform(MusicTag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'description' => $tag->description,
            'is_active' => $tag->is_active,
            'parent_id' => $tag->parent_id,
            'group_id' => $tag->group_id,
            'tracks_count' => $tag->pivot?->tracks_count,
            'percentage' => $tag->pivot?->percentage,
        ];
    }
}
