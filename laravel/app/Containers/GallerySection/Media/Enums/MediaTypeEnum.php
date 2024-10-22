<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum MediaTypeEnum: string
{
    use Arrayable;

    case PHOTO = 'photo';
    case VIDEO = 'video';
}
