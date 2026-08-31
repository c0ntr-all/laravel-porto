<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Strategies;

use App\Containers\GallerySection\Video\Contracts\VideoSourceContract;
use App\Ship\Helpers\WindowsPathHelper;

class WindowsDiskVideoSourceStrategy implements VideoSourceContract
{
    public function __construct(
        private readonly string $windowsPath,
    ) {
    }

    public function getDisk(): string
    {
        return (string) config('video.windows.disk', 'windows_f');
    }

    public function getRelativePath(): string
    {
        return WindowsPathHelper::relativeFromWindows($this->windowsPath);
    }

    public function getExtension(): string
    {
        return strtolower(pathinfo(str_replace('\\', '/', $this->windowsPath), PATHINFO_EXTENSION));
    }

    public function getOriginalName(): string
    {
        return basename(str_replace('\\', '/', WindowsPathHelper::normalizeWindows($this->windowsPath)));
    }

    public function getStoredExternalUrl(): ?string
    {
        return WindowsPathHelper::normalizeWindows($this->windowsPath);
    }
}
