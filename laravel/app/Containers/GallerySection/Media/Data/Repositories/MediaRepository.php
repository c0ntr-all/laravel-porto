<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Data\Repositories;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Media\Data\DTO\CreateMediaDto;
use App\Containers\GallerySection\Media\Models\Media;

class MediaRepository
{
    public function create(Album $album, CreateMediaDto $dto): Media
    {
        return $album->media()->create($dto->toArray());
    }
}
