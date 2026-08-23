<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tasks;

use App\Containers\MusicSection\Album\Data\DTO\CreateAlbumDto;
use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Album\Models\Album;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateAlbumTask extends ParentTask
{
    public function __construct(
        private readonly AlbumRepository $albumRepository
    )
    {
    }

    public function run(CreateAlbumDto $dto): Album
    {
        return $this->albumRepository->create($dto);
    }
}
