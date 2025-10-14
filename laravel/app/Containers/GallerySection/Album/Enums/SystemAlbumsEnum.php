<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum SystemAlbumsEnum: string
{
    use Arrayable;
    case UPLOAD = 'upload';
    case SAVE = 'save';
}
