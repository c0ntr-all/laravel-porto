<?php declare(strict_types=1);

namespace App\Ship\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum FileSourceEnum: string
{
    use Arrayable;

    case WINDOWS = 'windows';
    case WEB = 'web';
    case DEVICE = 'device';
}
