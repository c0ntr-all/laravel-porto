<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\Tasks;

use App\Containers\MusicSection\Playlist\Data\DTO\PlaylistCreateData;
use App\Containers\MusicSection\Playlist\Data\Repositories\PlaylistRepository;
use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreatePlaylistTask extends ParentTask
{
    public function __construct(
        private readonly PlaylistRepository $playlistRepository
    )
    {
    }

    /**
     * @param PlaylistCreateData $dto
     * @return Playlist
     */
    public function run(PlaylistCreateData $dto): Playlist
    {
        return $this->playlistRepository->createPlaylist($dto);
    }
}
