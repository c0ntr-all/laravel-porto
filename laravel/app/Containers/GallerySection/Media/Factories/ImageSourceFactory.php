<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Factories;

use App\Containers\GallerySection\Media\Contracts\ImageSourceContract;
use App\Containers\GallerySection\Media\Enums\MediaSourceEnum;
use App\Containers\GallerySection\Media\Strategies\LaravelDiskImageSourceStrategy;
use App\Containers\GallerySection\Media\Strategies\WebImageSourceStrategy;
use App\Containers\GallerySection\Media\Strategies\WindowsDiskImageSourceStrategy;

class ImageSourceFactory
{
    public static function create(string $filePath, string $sourceType): ImageSourceContract
    {
        return match ($sourceType) {
            MediaSourceEnum::WINDOWS->value => new WindowsDiskImageSourceStrategy($filePath),
            MediaSourceEnum::WEB->value => new WebImageSourceStrategy($filePath),
            MediaSourceEnum::DEVICE->value => new LaravelDiskImageSourceStrategy($filePath),
        };
    }
}
