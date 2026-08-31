<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tasks;

use App\Containers\GallerySection\Video\Data\DTO\UpdateVideoDto;
use App\Containers\GallerySection\Video\Data\Repositories\VideoRepository;
use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateVideoTask extends ParentTask
{
    public function __construct(
        private readonly VideoRepository $videoRepository
    ) {
    }

    public function run(Video $video, UpdateVideoDto $dto): Video
    {
        return $this->videoRepository->update($video, $dto);
    }
}
