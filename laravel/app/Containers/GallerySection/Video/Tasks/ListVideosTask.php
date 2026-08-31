<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tasks;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Video\Data\Repositories\VideoRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListVideosTask extends ParentTask
{
    public function __construct(
        private readonly VideoRepository $videoRepository
    ) {
    }

    public function run(Album $album): Collection
    {
        return $this->videoRepository->listByAlbum($album);
    }
}
