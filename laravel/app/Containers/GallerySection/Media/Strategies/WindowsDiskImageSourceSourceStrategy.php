<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Strategies;

readonly class WindowsDiskImageSourceSourceStrategy extends AbstractImageSourceStrategy
{
    public function getOriginalPath(): string
    {
        return $this->transformPath($this->getFullPath());
    }

    protected function transformPath(string $path): string
    {
        $windowsImagesRootFolder = config('app.windows_images_root_folder');

        return str_replace($windowsImagesRootFolder, '', $path);
    }
}
