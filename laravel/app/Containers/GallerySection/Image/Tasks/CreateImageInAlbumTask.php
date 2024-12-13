<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Data\DTO\CreateImageDto;
use App\Containers\GallerySection\Image\Data\Repositories\ImageRepository;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Parents\Tasks\Task;

class CreateImageInAlbumTask extends Task
{
    public function __construct(
        private readonly ImageRepository $imageRepository
    )
    {
    }

    public function run(Album $album, CreateImageDto $createImageDto): Image
    {
        return $this->imageRepository->create($album, $createImageDto);
    }
}
