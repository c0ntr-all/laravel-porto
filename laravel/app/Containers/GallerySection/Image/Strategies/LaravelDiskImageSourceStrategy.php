<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Strategies;

use Illuminate\Support\Facades\Storage;

class LaravelDiskImageSourceStrategy extends AbstractImageSourceStrategy
{
    public function getFullPath(): string
    {
        $originalPath = $this->getOriginalPath();

        return Storage::disk('public')->path($originalPath);
    }
}
