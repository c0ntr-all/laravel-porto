<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\GallerySection\Album\Models\Album;

class AlbumsWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'gallery.albums';
    }

    public function name(): string
    {
        return 'Альбомы галереи';
    }

    public function description(): string
    {
        return 'Альбомы Gallery.';
    }

    public function icon(): string
    {
        return 'collections';
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::HALF;
    }

    public function configSchema(): array
    {
        return $this->limitSchema(6, 20);
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        $albums = Album::query()
            ->withCount(['images', 'videos'])
            ->orderByDesc('id')
            ->limit($context->limit())
            ->get();

        $items = $albums->map(static fn (Album $album) => [
            'id' => (string) $album->id,
            'title' => $album->name,
            'subtitle' => (($album->images_count ?? 0) + ($album->videos_count ?? 0)) . ' файлов',
            'image' => $album->full_image,
            'href' => '/gallery/albums/' . $album->id,
        ])->values()->all();

        return $this->listPayload($context, $items, ['href' => '/gallery']);
    }
}
