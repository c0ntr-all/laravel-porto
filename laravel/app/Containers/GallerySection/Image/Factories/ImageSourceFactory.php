<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Factories;

use App\Containers\GallerySection\Image\Contracts\ImageSourceContract;
use App\Containers\GallerySection\Image\Strategies\LaravelDiskImageSourceStrategy;
use App\Containers\GallerySection\Image\Strategies\WebImageSourceStrategy;
use App\Containers\GallerySection\Image\Strategies\WindowsDiskImageSourceStrategy;
use App\Ship\Enums\FileSourceEnum;

class ImageSourceFactory
{
    public static function create(string $filePath, string $sourceType): ImageSourceContract
    {
        return match ($sourceType) {
            FileSourceEnum::WINDOWS->value => new WindowsDiskImageSourceStrategy($filePath),
            FileSourceEnum::WEB->value => new WebImageSourceStrategy($filePath),
            FileSourceEnum::DEVICE->value => new LaravelDiskImageSourceStrategy($filePath),
        };
    }
}
