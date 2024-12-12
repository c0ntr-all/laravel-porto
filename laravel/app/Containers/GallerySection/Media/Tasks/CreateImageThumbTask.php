<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Tasks;

use App\Containers\GallerySection\Media\Contracts\ImageSourceContract;
use App\Containers\GallerySection\Media\Enums\ImageThumbTypeEnum;
use App\Containers\GallerySection\Media\Services\PathGenerationService;
use App\Ship\Parents\Tasks\Task;
use InvalidArgumentException;

class CreateImageThumbTask extends Task
{
    private const int DEFAULT_QUALITY = 75;

    public function __construct(
        private readonly PathGenerationService $pathGenerationService
    )
    {
    }

    /**
     * @param ImageSourceContract $imageStrategy
     * @param string $thumbType
     * @param string $albumPath
     * @return string
     */
    public function run(ImageSourceContract $imageStrategy, string $thumbType, string $albumPath): string
    {
        if (!ImageThumbTypeEnum::tryFrom($thumbType)) {
            throw new InvalidArgumentException("Invalid thumbnail type: {$thumbType}");
        }

        $thumbTypeEnum = ImageThumbTypeEnum::from($thumbType);
        [$width, $height] = $thumbTypeEnum->getSize();

        $paths = $this->pathGenerationService->preparePathsForThumbnail(
            $albumPath,
            $imageStrategy->getBasename(),
            $thumbType,
        );

        $this->pathGenerationService->prepareFolder($paths['thumbs_folder_path']);

        $imageStrategy->getImage()
                      ->resize($width, $height)
                      ->save($paths['thumb_full_path'], self::DEFAULT_QUALITY);

        return $paths['thumb_path'];
    }
}
