<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Transformers;

use App\Containers\MusicSection\Tag\Models\MusicTag;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract
{
    public function transform(MusicTag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
        ];
    }
}
