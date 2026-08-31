<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Tasks;

use App\Containers\GallerySection\Album\Data\Repositories\AlbumRepository;
use App\Containers\GallerySection\Album\Models\Album;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class DeleteAlbumTask extends ParentTask
{
    public function __construct(
        private readonly AlbumRepository $albumRepository
    ) {
    }

    public function run(Album $album): bool
    {
        if ($album->isSystem()) {
            throw new UnprocessableEntityHttpException('System albums cannot be deleted.');
        }

        return $this->albumRepository->delete($album);
    }
}
