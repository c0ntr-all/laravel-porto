<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Tasks;

use App\Containers\GallerySection\Album\Data\Repositories\AlbumRepository;
use App\Containers\GallerySection\Album\Models\Album;
use App\Ship\Parents\Tasks\Task as ParentTask;

class GetAlbumTask extends ParentTask
{
    public function __construct(
        private readonly AlbumRepository $albumRepository
    ) {
    }

    /**
     * @param list<string> $with
     */
    public function run(Album $album, array $with = []): Album
    {
        return $this->albumRepository->getById($album, $with);
    }
}
