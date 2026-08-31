<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\Data\Repositories;

use App\Containers\MusicSection\Playlist\Data\DTO\PlaylistCreateData;
use App\Containers\MusicSection\Playlist\Data\DTO\PlaylistUpdateData;
use App\Containers\MusicSection\Playlist\Data\DTO\DeleteTrackFromPlaylistData;
use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Pagination\CursorPaginator;
use Spatie\QueryBuilder\AllowedFilter;

class PlaylistRepository
{
    /**
     * @param $userId
     * @return CursorPaginator
     */
    public function getWithCursor($userId = null): CursorPaginator
    {
        return QueryBuilder::for(Playlist::class)
                           ->allowedFilters([
                               AllowedFilter::partial('name'),
                           ])
                           ->allowedSorts(['name', 'created_at'])
                           ->allowedIncludes(['tracks'])
                           ->when($userId, fn($query) => $query->where('user_id', $userId))
                           ->orderByDesc('created_at')
                           ->orderByDesc('id')
                           ->cursorPaginate(12);
    }

    /**
     * @param PlaylistCreateData $dto
     * @return Playlist
     */
    public function createPlaylist(PlaylistCreateData $dto): Playlist
    {
        return Playlist::create($dto->toArray());
    }

    public function updatePlaylist(Playlist $playlist, PlaylistUpdateData $dto): Playlist
    {
        $playlist->update(collect($dto->toArray())->filter(fn (mixed $value) => $value !== null)->all());

        return $playlist;
    }

    public function deletePlaylist(Playlist $playlist): ?bool
    {
        return $playlist->delete();
    }

    /**
     * @param Playlist $playlist
     * @param DeleteTrackFromPlaylistData $dto
     * @return int
     */
    public function removeTrackFromPlaylist(Playlist $playlist, DeleteTrackFromPlaylistData $dto): int
    {
        return $playlist->tracks()->detach($dto->track_id);
    }
}
