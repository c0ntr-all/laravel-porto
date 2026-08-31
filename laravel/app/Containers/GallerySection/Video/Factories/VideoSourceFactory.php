<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Factories;

use App\Containers\GallerySection\Video\Contracts\VideoSourceContract;
use App\Containers\GallerySection\Video\Strategies\LaravelDiskVideoSourceStrategy;
use App\Containers\GallerySection\Video\Strategies\WebVideoSourceStrategy;
use App\Containers\GallerySection\Video\Strategies\WindowsDiskVideoSourceStrategy;
use App\Ship\Enums\FileSourceEnum;

class VideoSourceFactory
{
    public static function fromDevice(string $relativePath, string $originalName, ?string $disk = null): VideoSourceContract
    {
        return new LaravelDiskVideoSourceStrategy(
            $relativePath,
            $originalName,
            $disk ?? (string) config('filesystems.default'),
        );
    }

    public static function create(string $pathOrUrl, string $sourceType): VideoSourceContract
    {
        return match ($sourceType) {
            FileSourceEnum::WINDOWS->value => new WindowsDiskVideoSourceStrategy($pathOrUrl),
            FileSourceEnum::WEB->value => new WebVideoSourceStrategy($pathOrUrl),
            default => throw new \InvalidArgumentException("Unsupported video source: {$sourceType}"),
        };
    }
}
