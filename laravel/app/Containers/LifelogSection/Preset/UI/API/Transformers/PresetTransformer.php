<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\UI\API\Transformers;

use App\Containers\AppSection\Tag\UI\API\Transformers\TagTransformer;
use App\Containers\LifelogSection\Preset\Models\Preset;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class PresetTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'tags',
    ];

    public function transform(Preset $preset): array
    {
        return [
            'id' => (string) $preset->id,
            'uuid' => (string) $preset->uuid,
            'title' => $preset->title,
            'description' => $preset->description,
            'color' => $preset->color,
            'icon' => $preset->icon,
            'start_date' => $preset->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $preset->end_date?->format('Y-m-d H:i:s'),
            'rules' => $preset->rules?->toArray() ?? [],
            'created_at' => $preset->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeTags(Preset $preset): Collection
    {
        return $this->collection(
            $preset->tagsForUser($preset->user_id)->get(),
            new TagTransformer(),
            'tags'
        );
    }
}
