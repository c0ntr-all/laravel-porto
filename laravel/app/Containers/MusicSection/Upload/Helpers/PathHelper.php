<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Helpers;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class PathHelper
{
    /**
     * Преобразует Windows-путь (F:\Music\...) в Linux-путь (/var/www/.../storage/mnt/f/...).
     *
     * @param string $windowsPath
     * @return string
     */
    public static function windowsToLinux(string $windowsPath): string
    {
        if (!str_starts_with($windowsPath, 'F:\\')) {
            throw new InvalidArgumentException('Path must start with "F:\\"');
        }

        $relativePath = str_replace(['F:\\', '\\'], ['', '/'], $windowsPath);

        return Storage::disk('windows_f')->path($relativePath);
    }

    /**
     * Проверяет, доступен ли файл/папка по Windows-пути.
     *
     * @param string $linuxPath
     * @return bool
     */
    public static function exists(string $linuxPath): bool
    {
        return file_exists($linuxPath);
    }

    /**
     * Пример дополнительного метода: получение содержимого папки.
     *
     * @param string $windowsDir
     * @return array
     */
    public static function listFiles(string $windowsDir): array
    {
        $linuxDir = self::windowsToLinux($windowsDir);
        return scandir($linuxDir);
    }
}
