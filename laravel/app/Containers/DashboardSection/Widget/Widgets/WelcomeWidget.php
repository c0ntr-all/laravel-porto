<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetViewEnum;

class WelcomeWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'dashboard.welcome';
    }

    public function name(): string
    {
        return 'Приветствие';
    }

    public function description(): string
    {
        return 'Приветствие и краткий статус дня.';
    }

    public function icon(): string
    {
        return 'waving_hand';
    }

    public function view(): WidgetViewEnum
    {
        return WidgetViewEnum::WELCOME;
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::FULL;
    }

    public function supportedSizes(): array
    {
        return [WidgetSizeEnum::FULL, WidgetSizeEnum::HALF];
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        $hour = (int) now()->format('G');
        $greeting = match (true) {
            $hour < 6 => 'Доброй ночи',
            $hour < 12 => 'Доброе утро',
            $hour < 18 => 'Добрый день',
            default => 'Добрый вечер',
        };

        return WidgetPayload::make(
            type: $this->type(),
            title: $this->title($context),
            view: $this->view(),
            data: [
                'greeting' => $greeting,
                'name' => $context->user->name,
                'date' => now()->locale('ru')->translatedFormat('l, d F Y'),
            ],
        );
    }
}
