<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tasks;

use App\Containers\MusicSection\Track\Data\DTO\RateTrackData;
use App\Containers\MusicSection\Track\Data\Repositories\RateRepository;
use App\Containers\MusicSection\Track\Models\Rate;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class RateTrackTask extends ParentTask
{
    public function __construct(
        private readonly RateRepository $rateRepository
    )
    {
    }

    /**
     * @param Track $track
     * @param RateTrackData $dto
     * @return Rate
     */
    public function run(Track $track, RateTrackData $dto): Rate
    {
        return DB::transaction(function() use ($track, $dto) {
            return $this->rateRepository->create($track, $dto);
        });
    }
}
