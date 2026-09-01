<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetViewEnum;

class StaticTextWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'dashboard.static-text';
    }

    public function name(): string
    {
        return 'Текст';
    }

    public function description(): string
    {
        return 'Статичный текстовый блок. Содержимое задаётся в настройках виджета.';
    }

    public function icon(): string
    {
        return 'notes';
    }

    public function view(): WidgetViewEnum
    {
        return WidgetViewEnum::TEXT;
    }

    public function isStatic(): bool
    {
        return true;
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::THIRD;
    }

    public function configSchema(): array
    {
        return [
            'content' => [
                'type' => 'text',
                'label' => 'Текст',
                    'required' => false,
                    'default' => '',
            ],
        ];
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        return WidgetPayload::make(
            type: $this->type(),
            title: $this->title($context),
            view: $this->view(),
            data: [
                'content' => (string) $context->config('content', ''),
            ],
        );
    }
}
