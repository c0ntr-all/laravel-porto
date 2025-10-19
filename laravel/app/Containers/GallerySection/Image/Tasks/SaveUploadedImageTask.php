<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Image\Services\PathGenerationService;
use App\Ship\Helpers\ImageUpload;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Http\UploadedFile;
use phpDocumentor\Reflection\Exception;

class SaveUploadedImageTask extends Task
{
    public function __construct(
        private readonly PathGenerationService $pathGenerationService
    )
    {
    }

    /**
     * @param UploadedFile $file
     * @param string $basePath - Абсолютный путь до файла (без расширения)
     * @return string
     * @throws Exception
     */
    public function run(UploadedFile $file, string $basePath): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = basename($basePath) . '.' . $extension;
        $folder = dirname($basePath);

        $this->pathGenerationService->prepareFolder($folder);

        return ImageUpload::make()
                          ->setDiskName('public')
                          ->setFolder($folder)
                          ->setFilename($filename)
                          ->upload($file);
    }
}
