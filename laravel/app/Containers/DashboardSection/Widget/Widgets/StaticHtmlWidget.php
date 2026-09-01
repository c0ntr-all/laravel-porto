<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetViewEnum;

class StaticHtmlWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'dashboard.static-html';
    }

    public function name(): string
    {
        return 'HTML / вёрстка';
    }

    public function description(): string
    {
        return 'Статичный HTML-блок. Подходит для произвольной вёрстки на дашборде.';
    }

    public function icon(): string
    {
        return 'code';
    }

    public function view(): WidgetViewEnum
    {
        return WidgetViewEnum::HTML;
    }

    public function isStatic(): bool
    {
        return true;
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::HALF;
    }

    public function configSchema(): array
    {
        return [
            'html' => [
                'type' => 'html',
                'label' => 'HTML',
                'required' => false,
                'default' => '<p>Произвольная вёрстка</p>',
            ],
        ];
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        $html = (string) $context->config('html', '');

        return WidgetPayload::make(
            type: $this->type(),
            title: $this->title($context),
            view: $this->view(),
            html: $html,
            data: ['html' => $html],
        );
    }
}
