<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Factories;

use App\Containers\GallerySection\Image\Contracts\ImageSourceContract;
use App\Containers\GallerySection\Image\Enums\ImageSourceEnum;
use App\Containers\GallerySection\Image\Strategies\LaravelDiskImageSourceStrategy;
use App\Containers\GallerySection\Image\Strategies\WebImageSourceStrategy;
use App\Containers\GallerySection\Image\Strategies\WindowsDiskImageSourceStrategy;

class ImageSourceFactory
{
    public static function create(string $filePath, string $sourceType): ImageSourceContract
    {
        return match ($sourceType) {
            ImageSourceEnum::WINDOWS->value => new WindowsDiskImageSourceStrategy($filePath),
            ImageSourceEnum::WEB->value => new WebImageSourceStrategy($filePath),
            ImageSourceEnum::DEVICE->value => new LaravelDiskImageSourceStrategy($filePath),
        };
    }
}
