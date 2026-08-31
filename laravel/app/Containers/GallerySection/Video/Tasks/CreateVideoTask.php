<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tasks;

use App\Containers\GallerySection\Video\Data\DTO\CreateVideoDto;
use App\Containers\GallerySection\Video\Data\Repositories\VideoRepository;
use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateVideoTask extends ParentTask
{
    public function __construct(
        private readonly VideoRepository $videoRepository
    ) {
    }

    public function run(CreateVideoDto $createVideoDto): Video
    {
        return $this->videoRepository->create($createVideoDto);
    }
}
