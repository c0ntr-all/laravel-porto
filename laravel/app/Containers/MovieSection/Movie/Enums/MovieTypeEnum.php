<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum MovieTypeEnum: string
{
    use Arrayable;

    case MOVIE = 'movie';
    case TV_SERIES = 'tv_series';
    case SHOW = 'show';
}
