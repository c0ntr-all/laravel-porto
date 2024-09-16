<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tasks;

use App\Containers\MusicSection\Track\Data\DTO\AddTrackToPlaylistsData;
use App\Containers\MusicSection\Track\Data\Repositories\TrackRepository;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class AddTrackToPlaylistsTask extends ParentTask
{
    public function __construct(
        private readonly TrackRepository $trackRepository
    )
    {
    }

    /**
     * @param Track $track
     * @param AddTrackToPlaylistsData $dto
     * @return array
     */
    public function run(Track $track, AddTrackToPlaylistsData $dto): array
    {
        return DB::transaction(function() use ($track, $dto) {
            $pivotValues = ['user_id' => $dto->user_id];

            return $this->trackRepository->addTrackToPlaylists($track, $dto->playlist_ids, $pivotValues);
        });
    }
}
