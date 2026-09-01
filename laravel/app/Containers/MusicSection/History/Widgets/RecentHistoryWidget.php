<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\MusicSection\History\Models\History;

class RecentHistoryWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'music.recent-history';
    }

    public function name(): string
    {
        return 'Недавние треки';
    }

    public function description(): string
    {
        return 'Последние прослушанные композиции.';
    }

    public function icon(): string
    {
        return 'queue_music';
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::THIRD;
    }

    public function configSchema(): array
    {
        return $this->limitSchema(6, 20);
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        $history = History::query()
            ->where('user_id', $context->user->id)
            ->with(['track.artists'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($context->limit())
            ->get();

        $items = $history->map(function (History $item) {
            $track = $item->track;
            $artists = $track?->artists?->pluck('name')->filter()->implode(', ');

            return [
                'id' => (string) $item->id,
                'title' => $track?->name ?: 'Трек удалён',
                'subtitle' => $artists ?: null,
                'image' => $track?->full_image,
                'href' => $track ? '/music/tracks' : '/music',
            ];
        })->values()->all();

        return $this->listPayload($context, $items, ['href' => '/music/history']);
    }
}
