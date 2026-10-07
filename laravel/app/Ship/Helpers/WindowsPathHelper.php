<?php declare(strict_types=1);

namespace App\Ship\Helpers;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class WindowsPathHelper
{
    public static function normalizeWindows(string $path): string
    {
        $path = str_replace('/', '\\', trim($path));
        $path = preg_replace('/\\\\+/', '\\', $path) ?? $path;

        if (!preg_match('/^([A-Za-z]):\\\\(.*)$/', $path, $matches)) {
            throw new InvalidArgumentException('Path must be an absolute Windows path, e.g. F:\\Images\\photo.jpg');
        }

        $drive = strtoupper($matches[1]);
        $relative = rtrim($matches[2], '\\');

        return $drive . ':\\' . $relative;
    }

    public static function toLinux(string $windowsPath, string $disk): string
    {
        $windowsPath = self::normalizeWindows($windowsPath);

        if (PHP_OS_FAMILY === 'Windows') {
            return $windowsPath;
        }

        $relative = str_replace('\\', '/', substr($windowsPath, 3));

        return Storage::disk($disk)->path($relative);
    }

    public static function relativeFromWindows(string $windowsPath): string
    {
        $windowsPath = self::normalizeWindows($windowsPath);

        return str_replace('\\', '/', substr($windowsPath, 3));
    }

    public static function isUnderRoot(string $windowsPath, string $rootFolder): bool
    {
        try {
            $normalized = strtolower(self::normalizeWindows($windowsPath));
            $root = strtolower(rtrim(self::normalizeWindows($rootFolder), '\\')) . '\\';
        } catch (InvalidArgumentException) {
            return false;
        }

        return str_starts_with($normalized . '\\', $root);
    }

    public static function assertReadableFile(string $windowsPath, string $disk, string $rootFolder): string
    {
        if (!self::isUnderRoot($windowsPath, $rootFolder)) {
            throw new InvalidArgumentException('Path is outside of the allowed root folder.');
        }

        $absolutePath = self::toLinux($windowsPath, $disk);
        $realFile = realpath($absolutePath);

        if ($realFile === false || !is_file($realFile)) {
            throw new InvalidArgumentException('File was not found on disk.');
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $rootReal = realpath(rtrim(self::normalizeWindows($rootFolder), '\\'));
            if ($rootReal === false || !WindowsDiskRoot::contains($rootReal, $realFile)) {
                throw new InvalidArgumentException('File was not found on disk.');
            }

            return $realFile;
        }

        $libraryRoot = realpath(Storage::disk($disk)->path(''));

        if ($libraryRoot === false || !WindowsDiskRoot::contains($libraryRoot, $realFile)) {
            throw new InvalidArgumentException('File was not found on disk.');
        }

        return $realFile;
    }
}
