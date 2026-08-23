<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tasks;

use App\Containers\MusicSection\Track\Data\Repositories\TrackRepository;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FindTrackByPathTask extends ParentTask
{
    public function __construct(
        private readonly TrackRepository $trackRepository
    ) {
    }

    public function run(string $path): ?Track
    {
        return $this->trackRepository->findByPath($path);
    }
}
