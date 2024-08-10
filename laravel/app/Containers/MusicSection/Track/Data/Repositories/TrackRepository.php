<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\Repositories;

use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Pagination\CursorPaginator;

class TrackRepository
{
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
