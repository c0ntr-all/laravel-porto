<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Contracts;

use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetDefinition;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetCategoryEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetViewEnum;

interface WidgetContract
{
    public function type(): string;

    public function name(): string;

    public function description(): string;

    public function category(): WidgetCategoryEnum;

    public function icon(): string;

    public function view(): WidgetViewEnum;

    /**
     * @return list<WidgetSizeEnum>
     */
    public function supportedSizes(): array;

    public function defaultSize(): WidgetSizeEnum;

    /**
     * Schema used to validate instance config and to render a generic editor on the frontend.
     *
     * @return array<string, array<string, mixed>>
     */
    public function configSchema(): array;

    public function isStatic(): bool;

    public function definition(): WidgetDefinition;

    public function resolve(WidgetContext $context): WidgetPayload;
}
