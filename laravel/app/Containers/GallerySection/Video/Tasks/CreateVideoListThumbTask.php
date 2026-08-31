<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tasks;

use App\Containers\GallerySection\Video\Services\PathGenerationService;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\Log;

class CreateVideoListThumbTask extends ParentTask
{
    public function __construct(
        private readonly PathGenerationService $pathGenerationService,
    ) {
    }

    public function run(mixed $media, string $userId, string $albumId, string $fileId): void
    {
        $albumPath = $this->pathGenerationService->getAlbumFolderPath($userId, $albumId);
        $this->pathGenerationService->prepareFolder("{$albumPath}/thumbnails");
        $thumbPath = $this->pathGenerationService->getListThumbRelativePath($userId, $albumId, $fileId);

        try {
            $media->getFrameFromSeconds(1)
                  ->export()
                  ->toDisk((string) config('filesystems.default'))
                  ->save($thumbPath);
        } catch (\Throwable $exception) {
            Log::warning("Unable to create video thumbnail({$fileId}): " . $exception->getMessage());
        }
    }
}
