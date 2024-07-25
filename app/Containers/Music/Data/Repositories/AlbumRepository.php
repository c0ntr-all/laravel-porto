<?php

namespace App\Containers\Music\Data\Repositories;

use App\Containers\Music\Models\Album;
use App\Containers\Music\Models\Artist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;

class AlbumRepository
{
    public function getWithPaginate(): LengthAwarePaginator
    {
        return QueryBuilder::for(Album::class)
                           ->with(['tags'])
                           ->orderByDesc('created_at')
                           ->paginate(100);
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
