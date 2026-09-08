<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum TrackArtistRoleEnum: string
{
    use Arrayable;

    case Primary = 'primary';
    case Featured = 'featured';
}
