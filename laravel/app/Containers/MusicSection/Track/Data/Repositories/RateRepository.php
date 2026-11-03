<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\Repositories;

use App\Containers\MusicSection\Track\Data\DTO\RateTrackData;
use App\Containers\MusicSection\Track\Models\Rate;
use App\Containers\MusicSection\Track\Models\Track;

class RateRepository
{
    public function create(Track $track, RateTrackData $dto): Rate
    {
        if ($dto->rate <= 0) {
            $track->rate()->where('user_id', $dto->user_id)->delete();

            $rate = new Rate();
            $rate->user_id = $dto->user_id;
            $rate->track_id = $track->id;
            $rate->rate = 0;

            return $rate;
        }

        return $track->rate()->updateOrCreate([
            'user_id' => $dto->user_id,
        ], [
            'rate' => $dto->rate
        ]);
    }
}
