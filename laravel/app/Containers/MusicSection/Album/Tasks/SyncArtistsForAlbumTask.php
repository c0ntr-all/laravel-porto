<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tasks;

use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Album\Models\Album;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SyncArtistsForAlbumTask extends ParentTask
{
    public function __construct(
        private readonly AlbumRepository $albumRepository
    )
    {
    }

    public function run(Album $album, array $artistIds): array
    {
        return $this->albumRepository->syncArtists($album, $artistIds);
    }
}
