<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tasks;

use App\Containers\MusicSection\Track\Data\Repositories\TrackRepository;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteTrackTask extends ParentTask
{
    public function __construct(
        private readonly TrackRepository $trackRepository
    )
    {
    }

    public function run(Track $track): ?bool
    {
        return $this->trackRepository->delete($track);
    }
}
