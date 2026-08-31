<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Tasks;

use App\Containers\GallerySection\Album\Data\DTO\AlbumCreateData;
use App\Containers\GallerySection\Album\Data\Repositories\AlbumRepository;
use App\Containers\GallerySection\Album\Models\Album;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateAlbumTask extends ParentTask
{
    public function __construct(
        private readonly AlbumRepository $albumRepository
    ) {
    }

    public function run(AlbumCreateData $dto): Album
    {
        return $this->albumRepository->create($dto);
    }
}
