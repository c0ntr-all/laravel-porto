<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tasks;

use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

class ListAlbumsByNameTask extends Task
{
    public function __construct(
        private readonly AlbumRepository $albumRepository,
    )
    {
    }

    /**
     * @param Artist $artist
     * @param string $name
     * @return Album|null
     */
    public function run(Artist $artist, string $name): ?Album
    {
        return $this->albumRepository->listAlbumsByName($artist, $name);
    }
}
