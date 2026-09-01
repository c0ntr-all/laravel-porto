<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tests\Fakes;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetViewEnum;

class FakeWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'test.fake';
    }

    public function name(): string
    {
        return 'Fake';
    }

    public function view(): WidgetViewEnum
    {
        return WidgetViewEnum::TEXT;
    }

    public function supportedSizes(): array
    {
        return [WidgetSizeEnum::HALF, WidgetSizeEnum::THIRD];
    }

    public function configSchema(): array
    {
        return [
            'limit' => [
                'type' => 'integer',
                'min' => 1,
                'max' => 10,
                'default' => 3,
            ],
            'label' => [
                'type' => 'string',
                'required' => true,
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
                'content' => (string) $context->config('label'),
                'limit' => $context->limit(3, 10),
            ],
        );
    }
}
