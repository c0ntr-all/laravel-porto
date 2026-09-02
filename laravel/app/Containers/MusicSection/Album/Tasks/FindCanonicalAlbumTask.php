<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tasks;

use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FindCanonicalAlbumTask extends ParentTask
{
    public function __construct(
        private readonly AlbumRepository $albumRepository,
    ) {
    }

    public function run(Artist $artist, string $name, int $albumTypeId, ?string $edition = null): ?Album
    {
        return $this->albumRepository->findCanonical($artist, $name, $albumTypeId, $edition);
    }
}
