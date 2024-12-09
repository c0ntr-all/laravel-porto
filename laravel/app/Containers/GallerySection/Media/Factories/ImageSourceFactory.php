<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Factories;

use App\Containers\GallerySection\Media\Contracts\ImageSourceContract;
use App\Containers\GallerySection\Media\Enums\MediaSourceEnum;
use App\Containers\GallerySection\Media\Strategies\LaravelDiskImageSourceSourceStrategy;
use App\Containers\GallerySection\Media\Strategies\WebImageSourceSourceStrategy;
use App\Containers\GallerySection\Media\Strategies\WindowsDiskImageSourceSourceStrategy;

class ImageSourceFactory
{
    public static function create(string $filePath, string $sourceType): ImageSourceContract
    {
        return match ($sourceType) {
            MediaSourceEnum::WINDOWS->value => new WindowsDiskImageSourceSourceStrategy($filePath),
            MediaSourceEnum::WEB->value => new WebImageSourceSourceStrategy($filePath),
            MediaSourceEnum::DEVICE->value => new LaravelDiskImageSourceSourceStrategy($filePath),
        };
    }
}
