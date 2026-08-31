<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Tasks;

use App\Containers\GallerySection\Album\Data\Repositories\AlbumRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListAlbumsTask extends ParentTask
{
    public function __construct(
        private readonly AlbumRepository $albumRepository
    ) {
    }

    /**
     * @param list<string> $with
     */
    public function run(array $with = []): Collection
    {
        return $this->albumRepository->list($with);
    }
}
