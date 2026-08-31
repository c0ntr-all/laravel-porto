<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tasks;

use App\Containers\GallerySection\Video\Services\PathGenerationService;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SaveUploadedVideoTask extends ParentTask
{
    public function __construct(
        private readonly PathGenerationService $pathGenerationService,
    ) {
    }

    public function run(UploadedFile $file, string $userId, string $albumId, string $fileId): string
    {
        $extension = $file->extension();
        $folder = $this->pathGenerationService->getAlbumFolderPath($userId, $albumId);
        $this->pathGenerationService->prepareFolder($folder);

        return Storage::disk((string) config('filesystems.default'))
                      ->putFileAs($folder, $file, $fileId . '.' . $extension);
    }
}
