<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Tasks;

use App\Containers\GallerySection\Album\Data\Repositories\AlbumRepository;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

class ListAlbumsTask extends Task
{
    public function __construct(
        private readonly AlbumRepository $albumRepository
    )
    {
    }

    public function run(): Collection
    {
        return $this->albumRepository->getAlbums();
    }
}
