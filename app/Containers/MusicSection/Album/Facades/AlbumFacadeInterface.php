<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Facades;

use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Album\Data\Collections\AlbumCollection;
use Illuminate\Pagination\CursorPaginator;

interface AlbumFacadeInterface
{
    public function listAlbumsByArtist(Artist $artist): AlbumCollection|CursorPaginator;
}
