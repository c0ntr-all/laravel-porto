<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Contracts;

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
     * Get the path, that stored in DB like original
     *
     * @return string
     */
    public function getOriginalPath(): string;

    /**
     * Get the real image object
     *
     * @return Image
     */
    public function getImage(): Image;

    /**
     * Get filename with extension like f4344de45.jpg
     *
     * @return string
     */
    public function getBasename(): string;

    /**
     * Get filename without extension like f4344de45
     *
     * @return string
     */
    public function getFilename(): string;

    /**
     * Get the extension of uploading image
     *
     * @return string
     */
    public function getExtension(): string;
}
