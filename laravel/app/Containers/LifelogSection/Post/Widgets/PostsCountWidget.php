<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetViewEnum;
use App\Containers\LifelogSection\Post\Models\Post;

class PostsCountWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'lifelog.posts-count';
    }

    public function name(): string
    {
        return 'Количество постов';
    }

    public function description(): string
    {
        return 'Сколько записей уже есть в Lifelog.';
    }

    public function icon(): string
    {
        return 'countertops';
    }

    public function view(): WidgetViewEnum
    {
        return WidgetViewEnum::COUNT;
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::THIRD;
    }

    public function supportedSizes(): array
    {
        return [WidgetSizeEnum::THIRD, WidgetSizeEnum::HALF];
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        return $this->countPayload(
            $context,
            Post::query()->count(),
            'записей',
            ['href' => '/lifelog'],
        );
    }
}
