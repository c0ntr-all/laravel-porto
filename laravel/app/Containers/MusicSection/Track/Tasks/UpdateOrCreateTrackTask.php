<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tasks;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Track\Data\DTO\CreateTrackDto;
use App\Containers\MusicSection\Track\Data\Repositories\TrackRepository;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateOrCreateTrackTask extends ParentTask
{
    public function __construct(
        private readonly TrackRepository $trackRepository
    )
    {
    }

    /**
     * @param Album $album
     * @param CreateTrackDto $dto
     * @return Track
     */
    public function run(Album $album, CreateTrackDto $dto): Track
    {
        return $this->trackRepository->updateOrCreate($album, $dto);
    }
}
