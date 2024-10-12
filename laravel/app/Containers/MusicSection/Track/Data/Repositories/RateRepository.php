<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\Repositories;

use App\Containers\MusicSection\Track\Data\DTO\RateTrackData;
use App\Containers\MusicSection\Track\Models\Rate;
use App\Containers\MusicSection\Track\Models\Track;

class RateRepository
{
    public function create(Track $track, RateTrackData $dto): Rate
    {
        return $track->rate()->updateOrCreate([
            'user_id' => $dto->user_id,
        ], [
            'rate' => $dto->rate
        ]);
    }
}
