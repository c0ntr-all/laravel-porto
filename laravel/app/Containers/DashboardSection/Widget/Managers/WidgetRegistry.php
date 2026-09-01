<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Managers;

use App\Containers\DashboardSection\Widget\Contracts\WidgetContract;
use App\Containers\DashboardSection\Widget\Exceptions\WidgetNotFoundException;
use Illuminate\Support\Collection;

class WidgetRegistry
{
    /** @var array<string, WidgetContract> */
    private array $widgets = [];

    public function register(WidgetContract $widget): self
    {
        $this->widgets[$widget->type()] = $widget;

        return $this;
    }

    public function has(string $type): bool
    {
        return isset($this->widgets[$type]);
    }

    public function get(string $type): WidgetContract
    {
        if (!$this->has($type)) {
            throw new WidgetNotFoundException($type);
        }

        return $this->widgets[$type];
    }

    /**
     * @return list<string>
     */
    public function types(): array
    {
        return array_keys($this->widgets);
    }

    /**
     * @return Collection<string, WidgetContract>
     */
    public function all(): Collection
    {
        return collect($this->widgets);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function catalog(): array
    {
        return $this->all()
            ->map(static fn (WidgetContract $widget) => $widget->definition()->toArray())
            ->values()
            ->all();
    }
}
