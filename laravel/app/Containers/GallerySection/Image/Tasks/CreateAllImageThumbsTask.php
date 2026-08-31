<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Image\Contracts\ImageSourceContract;
use App\Containers\GallerySection\Image\Enums\ImageThumbTypeEnum;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateAllImageThumbsTask extends ParentTask
{
    public function __construct(
        private readonly CreateImageThumbTask $createImageThumbTask
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function run(ImageSourceContract $imageStrategy, string $albumPath, string $fileId): array
    {
        $thumbs = [];

        foreach (ImageThumbTypeEnum::cases() as $thumbType) {
            $thumbs[$thumbType->value] = $this->createImageThumbTask->run(
                $imageStrategy,
                $thumbType->value,
                $albumPath,
                $fileId,
            );
        }

        return $thumbs;
    }
}
