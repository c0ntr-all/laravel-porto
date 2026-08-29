<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Transformers;

use App\Containers\MusicSection\Tag\Models\MusicTagGroup;
use League\Fractal\TransformerAbstract;

class TagGroupTransformer extends TransformerAbstract
{
    public function transform(MusicTagGroup $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'slug' => $group->slug,
            'description' => $group->description,
            'is_system' => $group->is_system,
            'is_active' => $group->is_active,
            'display_order' => $group->display_order,
        ];
    }
}
