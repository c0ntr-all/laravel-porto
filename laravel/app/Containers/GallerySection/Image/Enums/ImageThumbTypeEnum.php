<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum ImageThumbTypeEnum: string
{
    use Arrayable;

    case LIST = 'list';
    case PREVIEW = 'preview';

    public function getSize(): array
    {
        return match ($this) {
            self::LIST => [265, 176],
            self::PREVIEW => [1280, 720]
        };
    }
}
