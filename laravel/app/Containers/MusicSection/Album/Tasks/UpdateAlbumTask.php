<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tasks;

use App\Containers\MusicSection\Album\Data\DTO\UpdateAlbumDto;
use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Album\Models\Album;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateAlbumTask extends ParentTask
{
    public function __construct(
        private readonly AlbumRepository $albumRepository
    )
    {
    }

    public function run(Album $album, UpdateAlbumDto $dto): Album
    {
        return $this->albumRepository->update($album, $dto);
    }
}
