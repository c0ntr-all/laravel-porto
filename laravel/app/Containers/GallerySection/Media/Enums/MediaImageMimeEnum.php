<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum MediaImageMimeEnum: string
{
    use Arrayable;

    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case webp = 'webp';
    case PNG = 'png';
    case GIF = 'gif';
    case BMP = 'bmp';
}
