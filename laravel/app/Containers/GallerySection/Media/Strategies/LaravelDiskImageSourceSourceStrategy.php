<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Strategies;

use Illuminate\Support\Facades\Storage;

readonly class LaravelDiskImageSourceSourceStrategy extends AbstractImageSourceStrategy
{
    public function getFullPath(): string
    {
        $originalPath = $this->getOriginalPath();

        return Storage::disk('public')->path($originalPath);
    }
}
