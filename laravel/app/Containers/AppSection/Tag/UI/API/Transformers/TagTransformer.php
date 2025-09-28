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
            'name' => $tag->name,
            'slug' => $tag->slug,
            'content' => $tag->content
        ];
    }
}
