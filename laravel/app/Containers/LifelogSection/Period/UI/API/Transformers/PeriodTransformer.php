<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\UI\API\Transformers;

use App\Containers\LifelogSection\Period\Models\Period;
use League\Fractal\TransformerAbstract;

class PeriodTransformer extends TransformerAbstract
{
    public function transform(Period $period): array
    {
        return [
            'id' => $period->id,
            'title' => $period->title,
            'color' => $period->color,
            'icon' => $period->icon,
            'start_post_id' => (string) $period->start_post_id,
            'end_post_id' => (string) $period->end_post_id,
            'start_date' => $period->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $period->end_date?->format('Y-m-d H:i:s'),
            'created_at' => $period->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
