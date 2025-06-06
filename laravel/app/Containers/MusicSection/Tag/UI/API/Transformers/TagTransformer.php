<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Transformers;

use App\Containers\MusicSection\Tag\Models\MusicTag;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'tags'
    ];

    protected array $defaultIncludes = [
        'tags'
    ];

    public function transform(MusicTag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'content' => $tag->content,
            'is_base' => $tag->is_base,
            'parent_id' => $tag->parent_id,
        ];
    }

    public function includeTags(MusicTag $tag): Collection
    {
        return $this->collection($tag->tags, new TagTransformer(), 'tags');
    }
}
