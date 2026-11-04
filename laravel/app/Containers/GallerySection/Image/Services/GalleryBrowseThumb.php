<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Services;

use App\Ship\Helpers\WindowsPathHelper;

class GalleryBrowseThumb
{
    public static function hash(string $windowsPath, int $size, int $mtime): string
    {
        return sha1(WindowsPathHelper::normalizeWindows($windowsPath) . '|' . $size . '|' . $mtime);
    }

    public static function relativePath(string $hash): string
    {
        return 'gallery-browse/' . $hash . '.jpg';
    }

    public static function url(string $hash): string
    {
        return url('/storage/' . self::relativePath($hash));
    }
}
