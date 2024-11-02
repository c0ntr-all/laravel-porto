<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum MediaVideoMimeEnum: string
{
    use Arrayable;

    case AVI = 'avi';
    case MP4 = 'mp4';
    case MKV = 'mkv';
}
