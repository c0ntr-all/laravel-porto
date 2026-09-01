<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\UI\API\Transformers;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\UI\API\Transformers\WidgetTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class DashboardTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'widgets',
    ];

    protected array $defaultIncludes = [
        'widgets',
    ];

    public function transform(Dashboard $dashboard): array
    {
        return [
            'id' => (string) $dashboard->id,
            'name' => $dashboard->name,
            'description' => $dashboard->description,
            'is_default' => (bool) $dashboard->is_default,
            'sort_order' => $dashboard->sort_order,
            'widgets_count' => $dashboard->relationLoaded('widgets')
                ? $dashboard->widgets->count()
                : $dashboard->widgets()->count(),
            'created_at' => $dashboard->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $dashboard->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeWidgets(Dashboard $dashboard): Collection
    {
        $widgets = $dashboard->relationLoaded('widgets')
            ? $dashboard->widgets
            : $dashboard->widgets()->get();

        return $this->collection($widgets, new WidgetTransformer(), 'dashboard_widgets')
                    ->setMeta(['count' => $widgets->count()]);
    }
}
