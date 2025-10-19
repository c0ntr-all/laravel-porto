<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Services;

use Intervention\Image\Image;

final class ImageAnalyzerService
{
    public function isVertical(Image $image): bool
    {
        return $image->height() > $image->width();
    }

    public function isHorizontal(Image $image): bool
    {
        return $image->width() >= $image->height();
    }

    public function getAspectRatio(Image $image): float
    {
        return $image->width() / $image->height();
    }

    public function isSquare(Image $image): bool
    {
        return $image->width() === $image->height();
    }
}
