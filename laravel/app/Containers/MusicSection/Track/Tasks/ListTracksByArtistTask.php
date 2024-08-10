<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tasks;

use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Track\Data\Repositories\TrackRepository;
use App\Ship\Parents\Tasks\Task;

class ListTracksByArtistTask extends Task
{
    public function __construct(
        private readonly TrackRepository $trackRepository,
    ) {
    }

    public function run(Artist $artist)
    {
        $albumIds = $artist->albums()->pluck('id')->toArray();

        return $this->trackRepository->listTracksByAlbumIdsWithCursor($albumIds);
    }
}
