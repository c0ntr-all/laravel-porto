<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum MediaSourceEnum: string
{
    use Arrayable;

    case WINDOWS = 'windows';
    case WEB = 'web';
    case DEVICE = 'device';
}
