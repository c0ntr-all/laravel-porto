<?php declare(strict_types=1);

namespace App\Containers\Music\Data\Repositories;

use App\Containers\Music\Models\Track;
use Illuminate\Pagination\CursorPaginator;

class TrackRepository
{
    public function getWithCursor(Filter|null $filter)
    {
        return Track::filter($filter)
                     ->orderByDesc('created_at')
                     ->cursorPaginate(12);
    }

    public function getWithPaginate(Filter|null $filter)
    {
        return Track::filter($filter)
                     ->orderByDesc('created_at')
                     ->paginate(100);
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
}
