<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum ImageSourceEnum: string
{
    use Arrayable;

    case WINDOWS = 'windows';
    case WEB = 'web';
    case DEVICE = 'device';
}
