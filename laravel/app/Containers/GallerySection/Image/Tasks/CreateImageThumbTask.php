<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Image\Contracts\ImageSourceContract;
use App\Containers\GallerySection\Image\Enums\ImageThumbTypeEnum;
use App\Containers\GallerySection\Image\Services\ImageAnalyzerService;
use App\Containers\GallerySection\Image\Services\PathGenerationService;
use App\Ship\Parents\Tasks\Task as ParentTask;
use InvalidArgumentException;

class CreateImageThumbTask extends ParentTask
{
    private const int DEFAULT_QUALITY = 75;

    public function __construct(
        private readonly PathGenerationService $pathGenerationService,
        private readonly ImageAnalyzerService $imageAnalyzerService
    ) {
    }

    public function run(
        ImageSourceContract $imageStrategy,
        string $thumbType,
        string $albumPath,
        string $fileId
    ): string {
        if (!ImageThumbTypeEnum::tryFrom($thumbType)) {
            throw new InvalidArgumentException("Invalid thumbnail type: {$thumbType}");
        }

        $thumbTypeEnum = ImageThumbTypeEnum::from($thumbType);

        $image = $imageStrategy->getImage();
        if ($this->imageAnalyzerService->isVertical($image)) {
            [$height, $width] = $thumbTypeEnum->getSize();
        } else {
            [$width, $height] = $thumbTypeEnum->getSize();
        }

        $basename = $fileId . '.' . $imageStrategy->getExtension();
        $paths = $this->pathGenerationService->preparePathsForThumbnail(
            $albumPath,
            $basename,
            $thumbType,
        );

        $this->pathGenerationService->prepareFolder($paths['thumbs_folder_path']);

        $imageStrategy->getImage()
                      ->scale($width, $height)
                      ->save($paths['thumb_full_path'], self::DEFAULT_QUALITY);

        return $paths['thumb_path'];
    }
}
