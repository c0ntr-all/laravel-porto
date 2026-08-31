<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Strategies;

use App\Containers\GallerySection\Video\Contracts\VideoSourceContract;

class LaravelDiskVideoSourceStrategy implements VideoSourceContract
{
    public function __construct(
        private readonly string $relativePath,
        private readonly string $originalName,
        private readonly string $disk,
    ) {
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    public function getExtension(): string
    {
        return strtolower(pathinfo($this->relativePath, PATHINFO_EXTENSION));
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getStoredExternalUrl(): ?string
    {
        return null;
    }
}
