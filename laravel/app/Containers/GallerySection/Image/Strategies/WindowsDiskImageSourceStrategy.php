<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Strategies;

use App\Ship\Helpers\WindowsPathHelper;

class WindowsDiskImageSourceStrategy extends AbstractImageSourceStrategy
{
    public function getFullPath(): string
    {
        $disk = (string) config('image.windows.disk', 'windows_f');

        return WindowsPathHelper::toLinux($this->path, $disk);
    }

    public function getOriginalPath(): string
    {
        return WindowsPathHelper::normalizeWindows($this->path);
    }
}
