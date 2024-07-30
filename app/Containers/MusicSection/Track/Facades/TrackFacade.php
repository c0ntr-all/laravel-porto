<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Facades;

use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Track\UI\Actions\ListTracksByArtistAction;
use App\Ship\Parents\Facades\Facade;
use Illuminate\Pagination\CursorPaginator;

final class TrackFacade extends Facade implements TrackFacadeInterface
{
    public function listTracksByArtist(Artist $artist): CursorPaginator
    {
        return ListTracksByArtistAction::run($artist);
    }
}
