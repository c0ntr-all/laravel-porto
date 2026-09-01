<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tasks;

use App\Containers\GallerySection\Album\Enums\SystemAlbumsEnum;
use App\Containers\GallerySection\Album\Tasks\GetSystemAlbumTask;
use App\Containers\GallerySection\Video\Data\DTO\CreateVideoDto;
use App\Containers\GallerySection\Video\Data\Repositories\VideoRepository;
use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Ramsey\Uuid\Uuid;

class SaveVideoToSaveAlbumTask extends ParentTask
{
    public function __construct(
        private readonly GetSystemAlbumTask $getSystemAlbumTask,
        private readonly VideoRepository $videoRepository,
        private readonly CreateVideoTask $createVideoTask,
        private readonly CopyVideoFilesTask $copyVideoFilesTask,
    ) {
    }

    public function run(Video $video, int $userId): Video
    {
        $saveAlbum = $this->getSystemAlbumTask->run(SystemAlbumsEnum::SAVE->value);
        $originId = $video->saved_from_id ?: (string) $video->id;

        $existing = $this->videoRepository->findSavedCopy($userId, $saveAlbum->id, $originId);
        if ($existing !== null) {
            return $existing;
        }

        $copy = $this->createVideoTask->run(CreateVideoDto::from([
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $userId,
            'album_id' => $saveAlbum->id,
            'source' => $video->source,
            'duration' => $video->duration,
            'original_name' => $video->original_name,
            'width' => $video->width,
            'height' => $video->height,
            'extension' => $video->extension,
            'external_url' => $video->external_url,
            'description' => $video->description,
            'saved_from_id' => $originId,
        ]));

        $this->copyVideoFilesTask->run($video, $copy);

        return $copy;
    }
}
