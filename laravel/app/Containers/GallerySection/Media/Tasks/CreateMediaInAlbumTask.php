<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Tasks;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Media\Data\DTO\CreateMediaDto;
use App\Containers\GallerySection\Media\Data\Repositories\MediaRepository;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

class CreateMediaInAlbumTask extends Task
{
    public function __construct(
        private readonly MediaRepository $mediaRepository
    )
    {
    }

    public function run(Album $album, CreateMediaDto $createMediaDto): Collection
    {
        return $this->mediaRepository->create($album, $createMediaDto);
    }
}
