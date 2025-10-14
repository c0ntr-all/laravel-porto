<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Tasks;

use App\Containers\GallerySection\Album\Data\Repositories\AlbumRepository;
use App\Containers\GallerySection\Album\Models\Album;
use App\Ship\Parents\Tasks\Task;

class GetSystemAlbumTask extends Task
{
    public function __construct(
        private readonly AlbumRepository $albumRepository
    )
    {
    }

    public function run(string $code): Album
    {
        return $this->albumRepository->getSystemAlbumByCode($code);
    }
}
