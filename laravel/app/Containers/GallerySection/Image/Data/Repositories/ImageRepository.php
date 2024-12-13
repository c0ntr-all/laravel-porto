<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Data\Repositories;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Data\DTO\CreateImageDto;
use App\Containers\GallerySection\Image\Models\Image;

class ImageRepository
{
    public function create(Album $album, CreateImageDto $dto): Image
    {
        return $album->images()->create($dto->toArray());
    }
}
