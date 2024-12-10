<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Tasks;

use App\Containers\GallerySection\Media\Contracts\ImageSourceContract;
use App\Containers\GallerySection\Media\Enums\ImageThumbTypeEnum;
use App\Ship\Parents\Tasks\Task;

class CreateAllImageThumbsTask extends Task
{
    public function __construct(
        private readonly CreateImageThumbTask $createImageThumbTask
    )
    {
    }

    public function run(ImageSourceContract $imageStrategy, string $albumPath): array
    {
        $thumbs = [];

        foreach (ImageThumbTypeEnum::cases() as $thumbType) {
            $thumbs[$thumbType->value] = $this->createImageThumbTask->run($imageStrategy, $thumbType->value, $albumPath);
        }

        return $thumbs;
    }
}
