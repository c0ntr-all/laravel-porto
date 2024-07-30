<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Facades;

use App\Containers\MusicSection\Album\Data\Collections\AlbumCollection;
use App\Containers\MusicSection\Album\UI\Actions\ListAlbumsByArtistAction;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\Facades\Facade;
use Illuminate\Pagination\CursorPaginator;

final class AlbumFacade extends Facade implements AlbumFacadeInterface
{
    public function listAlbumsByArtist(Artist $artist): AlbumCollection|CursorPaginator
    {
        return ListAlbumsByArtistAction::run($artist);
    }
}
