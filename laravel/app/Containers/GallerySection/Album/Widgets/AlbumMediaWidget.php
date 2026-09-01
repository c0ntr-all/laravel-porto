<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetViewEnum;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Models\Image;

class AlbumMediaWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'gallery.album-media';
    }

    public function name(): string
    {
        return 'Медиа альбома';
    }

    public function description(): string
    {
        return 'Изображения конкретного альбома. Укажите ID альбома в настройках.';
    }

    public function icon(): string
    {
        return 'photo_library';
    }

    public function view(): WidgetViewEnum
    {
        return WidgetViewEnum::MEDIA;
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::HALF;
    }

    public function configSchema(): array
    {
        return array_merge($this->limitSchema(8, 24), [
            'album_id' => [
                'type' => 'integer',
                'label' => 'ID альбома',
                'min' => 1,
            ],
        ]);
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        $albumId = (int) $context->config('album_id');
        if ($albumId < 1) {
            return WidgetPayload::error($this->type(), $this->title($context), 'Укажите ID альбома в настройках виджета.');
        }

        $album = Album::query()->find($albumId);

        if ($album === null) {
            return WidgetPayload::error($this->type(), $this->title($context), 'Альбом не найден.');
        }

        $images = Image::query()
            ->where('album_id', $album->id)
            ->orderByDesc('created_at')
            ->limit($context->limit())
            ->get();

        $items = $images->map(static fn (Image $image) => [
            'id' => (string) $image->id,
            'title' => $image->description ?: $album->name,
            'image' => $image->list_thumb_path,
            'href' => '/gallery/albums/' . $album->id,
        ])->values()->all();

        return WidgetPayload::make(
            type: $this->type(),
            title: $context->widget->title ?: $album->name,
            view: $this->view(),
            data: ['items' => $items],
            meta: [
                'count' => count($items),
                'href' => '/gallery/albums/' . $album->id,
                'album_id' => $album->id,
            ],
        );
    }
}
