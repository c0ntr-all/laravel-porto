<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\Repositories;

use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Pagination\CursorPaginator;

class TrackRepository
{
    public function getWithCursor(): CursorPaginator
    {
        return QueryBuilder::for(Track::class)
                           ->with(['tags'])
                           ->orderByDesc('created_at')
                           ->cursorPaginate(100);
    }
    /**
     * Get list of all tracks for Artist
     *
     * @param array $albumIds
     * @return CursorPaginator
     */
    public function listTracksByAlbumIdsWithCursor(array $albumIds): CursorPaginator
    {
        return Track::whereIn('album_id', $albumIds)->cursorPaginate(50);
    }

    public function addTrackToPlaylists(Track $track, array $playlists, array $pivotValues): array
    {
        return $track->playlists()->syncWithPivotValues($playlists, $pivotValues);
    }
}
