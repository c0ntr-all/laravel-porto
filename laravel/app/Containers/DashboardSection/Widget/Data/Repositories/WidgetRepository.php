<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Data\Repositories;

use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Data\DTO\CreateWidgetDto;
use App\Containers\DashboardSection\Widget\Data\DTO\UpdateWidgetDto;
use App\Containers\DashboardSection\Widget\Models\Widget;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;

class WidgetRepository
{
    public function list(Dashboard $dashboard): Collection
    {
        return $dashboard->widgets()->get();
    }

    public function nextSortOrder(Dashboard $dashboard): int
    {
        return (int) $dashboard->widgets()->max('sort_order') + 1;
    }

    public function create(CreateWidgetDto $dto): Widget
    {
        return Widget::create([
            'dashboard_id' => $dto->dashboard_id,
            'user_id' => $dto->user_id,
            'type' => $dto->type,
            'title' => $dto->title,
            'size' => $dto->size,
            'sort_order' => $dto->sort_order,
            'config' => $dto->config,
            'is_enabled' => $dto->is_enabled,
        ]);
    }

    public function update(Widget $widget, UpdateWidgetDto $dto): Widget
    {
        $attributes = [];

        foreach (['title', 'size', 'sort_order', 'config', 'is_enabled'] as $field) {
            if (!($dto->{$field} instanceof Optional)) {
                $attributes[$field] = $dto->{$field};
            }
        }

        if ($attributes !== []) {
            $widget->update($attributes);
        }

        return $widget->refresh();
    }

    public function delete(Widget $widget): bool
    {
        return (bool) $widget->delete();
    }

    /**
     * @param list<int> $ids
     */
    public function reorder(Dashboard $dashboard, array $ids): void
    {
        foreach ($ids as $index => $id) {
            Widget::query()
                ->where('dashboard_id', $dashboard->id)
                ->where('id', $id)
                ->update(['sort_order' => $index]);
        }
    }
}
