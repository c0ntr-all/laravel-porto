<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Data\Repositories\ImageRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListImagesTask extends ParentTask
{
    public function __construct(
        private readonly ImageRepository $imageRepository
    ) {
    }

    public function run(Album $album): Collection
    {
        return $this->imageRepository->listByAlbum($album);
    }
}
