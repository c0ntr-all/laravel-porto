<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\UI\API\Transformers;

use App\Containers\LifelogSection\Preset\Models\Preset;
use League\Fractal\TransformerAbstract;

class PresetTransformer extends TransformerAbstract
{
    public function transform(Preset $preset): array
    {
        return [
            'id' => $preset->id,
            'title' => $preset->title,
            'description' => $preset->description,
            'color' => $preset->color,
            'icon' => $preset->icon,
            'start_post_id' => (string) $preset->start_post_id,
            'end_post_id' => (string) $preset->end_post_id,
            'start_date' => $preset->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $preset->end_date?->format('Y-m-d H:i:s'),
            'created_at' => $preset->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
