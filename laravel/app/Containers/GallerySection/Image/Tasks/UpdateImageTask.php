<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Image\Data\DTO\UpdateImageDto;
use App\Containers\GallerySection\Image\Data\Repositories\ImageRepository;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateImageTask extends ParentTask
{
    public function __construct(
        private readonly ImageRepository $imageRepository
    ) {
    }

    public function run(Image $image, UpdateImageDto $dto): Image
    {
        return $this->imageRepository->update($image, $dto);
    }
}
