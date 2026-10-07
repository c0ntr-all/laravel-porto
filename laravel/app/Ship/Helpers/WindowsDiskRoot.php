<?php declare(strict_types=1);

namespace App\Ship\Helpers;

/**
 * Resolves the local root that stands in for the Windows F: drive.
 *
 * Docker/Linux PHP: storage/mnt/f (compose bind-mounts /mnt/f/{Music,Images,Video} there).
 * OSPanel/Windows PHP: the real drive, e.g. F:\ — paths like F:\Music stay native.
 */
final class WindowsDiskRoot
{
    public static function fromEnv(): string
    {
        return self::resolve(
            env('WINDOWS_F_DISK_ROOT'),
            PHP_OS_FAMILY,
            (string) env('MUSIC_LIBRARY_DRIVE', env('GALLERY_WINDOWS_DRIVE', 'F')),
        );
    }

    public static function resolve(mixed $configuredRoot, string $osFamily, string $drive = 'F'): string
    {
        $configuredRoot = is_string($configuredRoot) ? trim($configuredRoot) : '';
        if ($configuredRoot !== '') {
            return self::normalizeRoot($configuredRoot);
        }

        if (strcasecmp($osFamily, 'Windows') === 0) {
            return self::driveRoot($drive);
        }

        return storage_path('mnt/f');
    }

    public static function driveRoot(string $drive): string
    {
        $letter = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $drive) ?: 'F', 0, 1));

        return $letter.':\\';
    }

    public static function normalizeRoot(string $root): string
    {
        $root = trim($root);
        $asWindows = str_replace('/', '\\', $root);

        if (preg_match('/^[A-Za-z]:\\\\?$/', $asWindows) === 1) {
            return self::driveRoot($asWindows);
        }

        return $root;
    }

    public static function contains(string $root, string $path): bool
    {
        $normalize = static function (string $value): string {
            $value = str_replace('\\', '/', $value);
            $value = preg_replace('#/+#', '/', $value) ?? $value;
            $value = rtrim($value, '/');

            if (PHP_OS_FAMILY === 'Windows') {
                return strtolower($value);
            }

            return $value;
        };

        $root = $normalize($root);
        $path = $normalize($path);

        if ($root === '') {
            return false;
        }

        return $path === $root || str_starts_with($path, $root.'/');
    }
}
