<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Data\ValueObjects;

use App\Containers\DashboardSection\Widget\Enums\WidgetCategoryEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetViewEnum;
use App\Ship\Parents\ValueObjects\ValueObject;

final class WidgetDefinition extends ValueObject
{
    /**
     * @param list<WidgetSizeEnum> $supportedSizes
     * @param array<string, array<string, mixed>> $configSchema
     */
    public function __construct(
        public readonly string $type,
        public readonly string $name,
        public readonly string $description,
        public readonly WidgetCategoryEnum $category,
        public readonly string $icon,
        public readonly WidgetViewEnum $view,
        public readonly array $supportedSizes,
        public readonly WidgetSizeEnum $defaultSize,
        public readonly array $configSchema,
        public readonly bool $isStatic = false,
    ) {
    }

    public static function fromArray(array $data): static
    {
        $sizes = array_map(
            static fn (string $size) => WidgetSizeEnum::from($size),
            $data['supported_sizes'] ?? [],
        );

        return new self(
            type: (string) $data['type'],
            name: (string) $data['name'],
            description: (string) ($data['description'] ?? ''),
            category: WidgetCategoryEnum::from((string) $data['category']),
            icon: (string) ($data['icon'] ?? 'widgets'),
            view: WidgetViewEnum::from((string) ($data['view'] ?? WidgetViewEnum::LIST->value)),
            supportedSizes: $sizes,
            defaultSize: WidgetSizeEnum::from((string) ($data['default_size'] ?? WidgetSizeEnum::HALF->value)),
            configSchema: is_array($data['config_schema'] ?? null) ? $data['config_schema'] : [],
            isStatic: (bool) ($data['is_static'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'icon' => $this->icon,
            'view' => $this->view->value,
            'supported_sizes' => array_map(
                static fn (WidgetSizeEnum $size) => $size->value,
                $this->supportedSizes,
            ),
            'default_size' => $this->defaultSize->value,
            'config_schema' => $this->configSchema,
            'is_static' => $this->isStatic,
        ];
    }
}
