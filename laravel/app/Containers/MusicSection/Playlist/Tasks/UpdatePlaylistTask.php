<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\Tasks;

use App\Containers\MusicSection\Playlist\Data\DTO\PlaylistUpdateData;
use App\Containers\MusicSection\Playlist\Data\Repositories\PlaylistRepository;
use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdatePlaylistTask extends ParentTask
{
    public function __construct(
        private readonly PlaylistRepository $playlistRepository
    )
    {
    }

    public function run(Playlist $playlist, PlaylistUpdateData $dto): Playlist
    {
        return $this->playlistRepository->updatePlaylist($playlist, $dto);
    }
}
