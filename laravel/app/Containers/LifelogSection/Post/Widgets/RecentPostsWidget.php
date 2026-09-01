<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\LifelogSection\Post\Models\Post;

class RecentPostsWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'lifelog.recent-posts';
    }

    public function name(): string
    {
        return 'Последние посты';
    }

    public function description(): string
    {
        return 'Недавние записи Lifelog.';
    }

    public function icon(): string
    {
        return 'history_edu';
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::THIRD;
    }

    public function configSchema(): array
    {
        return $this->limitSchema(5, 20);
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        $posts = Post::query()
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit($context->limit())
            ->get();

        $items = $posts->map(static fn (Post $post) => [
            'id' => (string) $post->id,
            'title' => $post->title ?: 'Без названия',
            'subtitle' => $post->date?->format('d.m.Y'),
            'href' => '/lifelog',
        ])->values()->all();

        return $this->listPayload($context, $items, ['href' => '/lifelog']);
    }
}
