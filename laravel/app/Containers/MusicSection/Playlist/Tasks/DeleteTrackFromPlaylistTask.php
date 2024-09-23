<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\Tasks;

use App\Containers\MusicSection\Playlist\Data\DTO\DeleteTrackFromPlaylistData;
use App\Containers\MusicSection\Playlist\Data\Repositories\PlaylistRepository;
use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Ship\Exceptions\DeleteResourceFailedException;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteTrackFromPlaylistTask extends ParentTask
{
    public function __construct(
        private readonly PlaylistRepository $playlistRepository
    )
    {
    }

    /**
     * @throws DeleteResourceFailedException
     */
    public function run(Playlist $playlist, DeleteTrackFromPlaylistData $dto): int
    {
        try {
            $result = $this->playlistRepository->removeTrackFromPlaylist($playlist, $dto);

            if (!$result) {
                throw new DeleteResourceFailedException();
            }

            return $result;
        } catch (\Exception $e) {
            throw new DeleteResourceFailedException();
        }
    }
}
