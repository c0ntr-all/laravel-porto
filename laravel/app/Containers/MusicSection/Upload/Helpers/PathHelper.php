<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Helpers;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class PathHelper
{
    public static function normalizeWindows(string $path): string
    {
        $path = str_replace('/', '\\', trim($path));
        $path = preg_replace('/\\\\+/', '\\', $path) ?? $path;

        if (!preg_match('/^([A-Za-z]):\\\\(.*)$/', $path, $matches)) {
            throw new InvalidArgumentException('Path must be an absolute Windows path, e.g. F:\\Music\\Artist');
        }

        $drive = strtoupper($matches[1]);
        $relative = rtrim($matches[2], '\\');

        return $drive . ':\\' . $relative;
    }

    public static function toLinux(string $windowsPath): string
    {
        $windowsPath = self::normalizeWindows($windowsPath);
        $drive = strtoupper($windowsPath[0]);
        $configuredDrive = strtoupper((string) config('music_upload.drive', 'F'));

        if ($drive !== $configuredDrive) {
            throw new InvalidArgumentException("Only {$configuredDrive}: drive is mounted for the music library.");
        }

        $relative = str_replace('\\', '/', substr($windowsPath, 3));

        return Storage::disk(self::disk())->path($relative);
    }

    public static function toWindows(string $linuxPath): string
    {
        $normalizedLinux = str_replace('\\', '/', $linuxPath);
        $root = rtrim(str_replace('\\', '/', Storage::disk(self::disk())->path('')), '/');

        if (preg_match('/^[A-Za-z]:\\\\/', str_replace('/', '\\', $linuxPath))) {
            return self::normalizeWindows($linuxPath);
        }

        if (!str_starts_with($normalizedLinux, $root)) {
            throw new InvalidArgumentException('Linux path is outside of the mounted music library disk.');
        }

        $relative = ltrim(substr($normalizedLinux, strlen($root)), '/');
        $drive = strtoupper((string) config('music_upload.drive', 'F'));

        return self::normalizeWindows($drive . ':\\' . str_replace('/', '\\', $relative));
    }

    public static function exists(string $linuxPath): bool
    {
        return is_dir($linuxPath) || is_file($linuxPath);
    }

    public static function basename(string $windowsPath): string
    {
        $normalized = self::normalizeWindows($windowsPath);

        return basename(str_replace('\\', '/', $normalized));
    }

    public static function dirname(string $windowsPath): string
    {
        $normalized = self::normalizeWindows($windowsPath);
        $dir = dirname(str_replace('\\', '/', $normalized));

        return self::normalizeWindows(str_replace('/', '\\', $dir));
    }

    private static function disk(): string
    {
        return (string) config('music_upload.disk', 'windows_f');
    }
}
