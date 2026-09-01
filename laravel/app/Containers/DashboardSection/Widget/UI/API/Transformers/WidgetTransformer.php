<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\API\Transformers;

use App\Containers\DashboardSection\Widget\Models\Widget;
use League\Fractal\TransformerAbstract;

class WidgetTransformer extends TransformerAbstract
{
    public function transform(Widget $widget): array
    {
        return [
            'id' => (string) $widget->id,
            'dashboard_id' => (string) $widget->dashboard_id,
            'type' => $widget->type,
            'title' => $widget->title,
            'size' => $widget->size->value,
            'sort_order' => $widget->sort_order,
            'config' => $widget->config ?? [],
            'is_enabled' => (bool) $widget->is_enabled,
            'payload' => $widget->resolvedPayload,
            'created_at' => $widget->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $widget->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
