<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Abstracts;

use App\Containers\DashboardSection\Widget\Contracts\WidgetContract;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetDefinition;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetCategoryEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetViewEnum;

/**
 * Base class for dashboard widgets.
 *
 * To add a custom widget:
 * 1. Create a class in `{Section}/{Container}/Widgets/{Name}Widget.php`
 * 2. Extend this class and implement `type()`, `name()` and `resolve()`
 * 3. Optionally override category, sizes, config schema and view
 *
 * The widget is auto-discovered on boot. No extra registration is required.
 */
abstract class AbstractWidget implements WidgetContract
{
    abstract public function type(): string;

    abstract public function name(): string;

    public function description(): string
    {
        return '';
    }

    public function category(): WidgetCategoryEnum
    {
        $prefix = explode('.', $this->type(), 2)[0] ?? '';

        return WidgetCategoryEnum::tryFrom($prefix) ?? WidgetCategoryEnum::CUSTOM;
    }

    public function icon(): string
    {
        return 'widgets';
    }

    public function view(): WidgetViewEnum
    {
        return WidgetViewEnum::LIST;
    }

    public function supportedSizes(): array
    {
        return WidgetSizeEnum::cases();
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::HALF;
    }

    public function configSchema(): array
    {
        return [];
    }

    public function isStatic(): bool
    {
        return false;
    }

    public function definition(): WidgetDefinition
    {
        return new WidgetDefinition(
            type: $this->type(),
            name: $this->name(),
            description: $this->description(),
            category: $this->category(),
            icon: $this->icon(),
            view: $this->view(),
            supportedSizes: $this->supportedSizes(),
            defaultSize: $this->defaultSize(),
            configSchema: $this->configSchema(),
            isStatic: $this->isStatic(),
        );
    }

    protected function title(WidgetContext $context): string
    {
        $custom = $context->widget->title;

        return is_string($custom) && $custom !== '' ? $custom : $this->name();
    }

    /**
     * @param list<array<string, mixed>> $items
     * @param array<string, mixed> $meta
     */
    protected function listPayload(
        WidgetContext $context,
        array $items,
        array $meta = [],
    ): WidgetPayload {
        return WidgetPayload::make(
            type: $this->type(),
            title: $this->title($context),
            view: WidgetViewEnum::LIST,
            data: ['items' => $items],
            meta: array_merge(['count' => count($items)], $meta),
        );
    }

    /**
     * @param array<string, mixed> $meta
     */
    protected function countPayload(
        WidgetContext $context,
        int $value,
        string $label,
        array $meta = [],
    ): WidgetPayload {
        return WidgetPayload::make(
            type: $this->type(),
            title: $this->title($context),
            view: WidgetViewEnum::COUNT,
            data: [
                'value' => $value,
                'label' => $label,
            ],
            meta: $meta,
        );
    }

    protected function limitSchema(int $default = 5, int $max = 20): array
    {
        return [
            'limit' => [
                'type' => 'integer',
                'label' => 'Количество записей',
                'min' => 1,
                'max' => $max,
                'default' => $default,
            ],
        ];
    }
}
