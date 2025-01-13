<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\DTO;

use App\Ship\Parents\DTO\Data;

class RateTrackData extends Data
{
    public int $user_id;
    public int $rate;

    public function __construct(
    ) {
    }
}
