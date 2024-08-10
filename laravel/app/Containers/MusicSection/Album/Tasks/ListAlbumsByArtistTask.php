<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tasks;

use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\Tasks\Task;

class ListAlbumsByArtistTask extends Task
{
    public function __construct(
        private readonly AlbumRepository $albumRepository,
    ) {
    }

    public function run(Artist $artist)
    {
        return $this->albumRepository->listAlbumsByArtist($artist);
    }
}
