<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Data\Repositories;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Media\Data\DTO\CreateMediaDto;
use Illuminate\Database\Eloquent\Collection;

class MediaRepository
{
    public function create(Album $album, CreateMediaDto $dto): Collection
    {
        return $album->media()->create($dto->toArray());
    }
}
