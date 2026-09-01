<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\MusicSection\Playlist\Models\Playlist;

class PlaylistsWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'music.playlists';
    }

    public function name(): string
    {
        return 'Плейлисты';
    }

    public function description(): string
    {
        return 'Ваши музыкальные плейлисты.';
    }

    public function icon(): string
    {
        return 'library_music';
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
        $playlists = Playlist::query()
            ->user($context->user->id)
            ->withCount('tracks')
            ->orderByDesc('id')
            ->limit($context->limit())
            ->get();

        $items = $playlists->map(static fn (Playlist $playlist) => [
            'id' => (string) $playlist->id,
            'title' => $playlist->name,
            'subtitle' => $playlist->tracks_count . ' треков',
            'image' => $playlist->full_image,
            'href' => '/music/playlists/' . $playlist->id,
        ])->values()->all();

        return $this->listPayload($context, $items, ['href' => '/music/playlists']);
    }
}
