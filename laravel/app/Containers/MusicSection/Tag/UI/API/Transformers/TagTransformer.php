<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Transformers;

use App\Containers\MusicSection\Tag\Models\MusicTag;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'tags',
        'group',
    ];

    protected array $defaultIncludes = [
        'tags',
    ];

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

    public function includeTags(MusicTag $tag): Collection
    {
        return $this->collection($tag->tags, new TagTransformer(), 'tags');
    }

    public function includeGroup(MusicTag $tag): Item|NullResource
    {
        if (!$tag->group) {
            return $this->null();
        }

        return $this->item($tag->group, new TagGroupTransformer(), 'tag_groups');
    }
}
