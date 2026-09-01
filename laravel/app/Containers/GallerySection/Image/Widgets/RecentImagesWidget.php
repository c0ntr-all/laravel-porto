<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Enums\WidgetViewEnum;
use App\Containers\GallerySection\Image\Models\Image;

class RecentImagesWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'gallery.recent-images';
    }

    public function name(): string
    {
        return 'Последние фото';
    }

    public function description(): string
    {
        return 'Недавно добавленные изображения галереи.';
    }

    public function icon(): string
    {
        return 'photo';
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
        return $this->limitSchema(8, 24);
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        $images = Image::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($context->limit())
            ->get();

        $items = $images->map(static fn (Image $image) => [
            'id' => (string) $image->id,
            'title' => $image->description ?: 'Изображение',
            'image' => $image->list_thumb_path,
            'href' => '/gallery/albums/' . $image->album_id,
        ])->values()->all();

        return WidgetPayload::make(
            type: $this->type(),
            title: $this->title($context),
            view: $this->view(),
            data: ['items' => $items],
            meta: [
                'count' => count($items),
                'href' => '/gallery',
            ],
        );
    }
}
