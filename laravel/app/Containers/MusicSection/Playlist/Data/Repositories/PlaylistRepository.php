<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\Data\Repositories;

use App\Containers\MusicSection\Playlist\Data\DTO\PlaylistCreateData;
use App\Containers\MusicSection\Playlist\Data\DTO\DeleteTrackFromPlaylistData;
use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Pagination\CursorPaginator;

class PlaylistRepository
{
    /**
     * @param $userId
     * @return CursorPaginator
     */
    public static function getWithCursor($userId = null): CursorPaginator
    {
        return QueryBuilder::for(Playlist::class)
                           ->when($userId, fn($query) => $query->where('user_id', $userId))
                           ->orderByDesc('created_at')
                           ->cursorPaginate(12);
    }

    /**
     * @param PlaylistCreateData $dto
     * @return mixed
     */
    public function createPlaylist(PlaylistCreateData $dto): Playlist
    {
        return Playlist::create($dto->toArray());
    }

    /**
     * @param Playlist $playlist
     * @param DeleteTrackFromPlaylistData $dto
     * @return int
     */
    public function removeTrackFromPlaylist(Playlist $playlist, DeleteTrackFromPlaylistData $dto): int
    {
        return $playlist->tracks()->where('track_id', $dto->track_id)->delete();
    }
}
