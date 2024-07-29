<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Facades;

use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Track\Data\Collections\TrackCollection;

interface TrackFacadeInterface
{
    public function listTracksByArtist(Artist $artist): TrackCollection;
}
