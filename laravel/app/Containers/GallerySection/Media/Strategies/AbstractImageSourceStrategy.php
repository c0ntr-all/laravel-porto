<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Strategies;

use App\Containers\GallerySection\Media\Contracts\ImageSourceContract;
use App\Ship\Parents\Image\Image;
use Spatie\Image\Exceptions\CouldNotLoadImage;

abstract readonly class AbstractImageSourceStrategy implements ImageSourceContract
{
    public function __construct(
        private string $path
    )
    {
    }

    public function getFullPath(): string {
        return $this->path;
    }

    public function getOriginalPath(): string {
        return $this->path;
    }

    /**
     * @throws CouldNotLoadImage
     */
    public function getImage(): Image
    {
        $fullPath = $this->getFullPath();

        return Image::load($fullPath);
    }
}
