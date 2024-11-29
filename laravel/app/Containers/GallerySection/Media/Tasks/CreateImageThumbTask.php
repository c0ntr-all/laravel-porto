<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Tasks;

use App\Containers\GallerySection\Media\Enums\ImageThumbTypeEnum;
use App\Ship\Parents\Tasks\Task;
use Exception;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Spatie\Image\Exceptions\CouldNotLoadImage;
use Spatie\Image\Image;

class CreateImageThumbTask extends Task
{
    /**
     * @param string $pathToFile
     * @param string $thumbType
     * @return string
     * @throws CouldNotLoadImage
     * @throws Exception
     */
    public function run(string $pathToFile, string $thumbType): string
    {
        if (!ImageThumbTypeEnum::tryFrom($thumbType)) {
            throw new InvalidArgumentException("Invalid thumbnail type: {$thumbType}");
        }

        [$width, $height] = $this->getSize($thumbType);
        $fullPath = Storage::disk('public')->path($pathToFile);
        $newFileName = $this->getNewFileName($fullPath, $thumbType);
        $newFileFullPath = $this->getNewFileFullPath($fullPath, $newFileName);

        Image::load($fullPath)->resize($width, $height)
                              ->optimize()
                              ->save($newFileFullPath);

        return $this->getNewFilePath($pathToFile, $newFileName);

    }

    private function getSize(string $thumbType): array
    {
        $thumbType = ImageThumbTypeEnum::from($thumbType);

        return $thumbType->getSize();
    }

    private function getNewFileFullPath(string $fullPath, string $newFileName): string
    {
        $folder = $this->getThumbnailsFolder($fullPath);

        $this->createThumbnailsFolder($folder);

        return $folder . '/' . $newFileName;
    }

    private function getNewFileName(string $fullPath, string $thumbType): string
    {
        $info = pathinfo($fullPath);

        return sprintf('%s_%s_thumbnail.%s', $info['filename'], $thumbType, $info['extension']);
    }

    private function createThumbnailsFolder(string $folder): void
    {
        if (!is_dir($folder)) {
            mkdir($folder);
        }
    }

    private function getNewFilePath(string $pathToFile, string $newFileName): string
    {
        $folder = $this->getThumbnailsFolder($pathToFile);

        return $folder . '/' . $newFileName;
    }

    private function getThumbnailsFolder(string $fullPath): string
    {
        return dirname($fullPath) . '/thumbnails';
    }
}
