<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tasks;

use App\Containers\MusicSection\Track\Data\Repositories\TrackRepository;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SyncArtistsForTrackTask extends ParentTask
{
    public function __construct(
        private readonly TrackRepository $trackRepository
    )
    {
    }

    public function run(Track $track, array $artistIds): array
    {
        return $this->trackRepository->syncArtists($track, $artistIds);
    }
}
