<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Image\Data\Repositories\ImageRepository;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteImageTask extends ParentTask
{
    public function __construct(
        private readonly ImageRepository $imageRepository,
        private readonly DeleteImageFilesTask $deleteImageFilesTask,
    ) {
    }

    public function run(Image $image): bool
    {
        $this->deleteImageFilesTask->run($image);

        return $this->imageRepository->delete($image);
    }
}
