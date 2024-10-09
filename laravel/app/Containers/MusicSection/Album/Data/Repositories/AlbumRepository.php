<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Data\Repositories;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\CursorPaginator;
use Spatie\QueryBuilder\QueryBuilder;

class AlbumRepository
{
    public function getWithCursor(): CursorPaginator
    {
        return QueryBuilder::for(Album::class)
                           ->with(['tags'])
                           ->orderByDesc('created_at')
                           ->cursorPaginate(100);
    }

    /**
     * Get list of core albums (without versions) for Artist
     *
     * @param Artist $artist
     * @return mixed
     */
    public function listAlbumsByArtist(Artist $artist): Collection
    {
        return $artist->albums()
                      ->whereNull('parent_id')
                      ->get();
    }
}
