<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\UI\API\Transformers;

use App\Containers\AppSection\Tag\Models\Tag;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract
{
    public function transform(Tag $tag): array
    {
        return [
            'id' => $tag->id,
            'user_id' => $tag->user_id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'icon' => $tag->icon,
            'color' => $tag->color,
            'description' => $tag->description,
            'parent_id' => $tag->parent_id,
        ];
    }
}
