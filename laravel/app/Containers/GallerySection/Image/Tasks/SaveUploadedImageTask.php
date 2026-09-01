<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Image\Services\PathGenerationService;
use App\Ship\Helpers\ImageUpload;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Http\UploadedFile;

class SaveUploadedImageTask extends Task
{
    public function __construct(
        private readonly PathGenerationService $pathGenerationService
    )
    {
    }

    /**
     * @param UploadedFile $file
     * @param string $basePath Relative path without extension
     * @return string
     */
    public function run(UploadedFile $file, string $basePath): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = basename($basePath) . '.' . $extension;
        $folder = dirname($basePath);

        $this->pathGenerationService->prepareFolder($folder);

        return ImageUpload::make()
                          ->setDiskName((string) config('image.disk', 'public'))
                          ->setFolder($folder)
                          ->setFilename($filename)
                          ->upload($file);
    }
}
