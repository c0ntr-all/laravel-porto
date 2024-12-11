<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Contracts;

use Intervention\Image\Image;

interface ImageSourceContract
{
    /**
     * Get the absolute path of image
     *
     * @return string
     */
    public function getFullPath(): string;

    /**
     * Get the real image object
     *
     * @return Image
     */
    public function getImage(): Image;

    /**
     * Get the path, that stored in DB like original
     *
     * @return string
     */
    public function getOriginalPath(): string;
}
