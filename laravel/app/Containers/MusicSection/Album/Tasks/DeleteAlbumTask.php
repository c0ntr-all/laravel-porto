<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tasks;

use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Album\Models\Album;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteAlbumTask extends ParentTask
{
    public function __construct(
        private readonly AlbumRepository $albumRepository
    )
    {
    }

    public function run(Album $album): ?bool
    {
        return $this->albumRepository->delete($album);
    }
}
