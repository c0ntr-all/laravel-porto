<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Tasks;

use App\Containers\GallerySection\Album\Data\DTO\AlbumUpdateData;
use App\Containers\GallerySection\Album\Data\Repositories\AlbumRepository;
use App\Containers\GallerySection\Album\Models\Album;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateAlbumTask extends ParentTask
{
    public function __construct(
        private readonly AlbumRepository $albumRepository
    ) {
    }

    public function run(Album $album, AlbumUpdateData $dto): Album
    {
        return $this->albumRepository->update($album, $dto);
    }
}
