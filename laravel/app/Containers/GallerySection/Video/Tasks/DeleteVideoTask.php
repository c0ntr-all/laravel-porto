<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tasks;

use App\Containers\GallerySection\Video\Data\Repositories\VideoRepository;
use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteVideoTask extends ParentTask
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly DeleteVideoFilesTask $deleteVideoFilesTask,
    ) {
    }

    public function run(Video $video): bool
    {
        $this->deleteVideoFilesTask->run($video);

        return $this->videoRepository->delete($video);
    }
}
