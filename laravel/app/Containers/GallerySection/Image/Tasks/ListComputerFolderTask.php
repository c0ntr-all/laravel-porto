<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Image\Enums\ImageMimeEnum;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Helpers\WindowsPathHelper;
use App\Ship\Parents\Tasks\Task as ParentTask;

class ListComputerFolderTask extends ParentTask
{
    /**
     * One directory level. Names and stat only — no pixel reads.
     *
     * @return array{
     *     path: string,
     *     name: string,
     *     breadcrumbs: list<array{name: string, path: string}>,
     *     folders: list<array{name: string, path: string}>,
     *     files: list<array<string, mixed>>,
     *     total: int,
     *     page: int,
     *     per_page: int
     * }
     */
    public function run(string $windowsPath, int $userId, int $page, int $perPage): array
    {
        $disk = (string) config('image.windows.disk', 'windows_f');
        $root = (string) config('image.windows.root_folder', config('app.windows_images_root_folder'));
        $normalized = WindowsPathHelper::normalizeWindows($windowsPath);
        $linuxPath = WindowsPathHelper::assertReadableDirectory($normalized, $disk, $root);

        $entries = scandir($linuxPath, SCANDIR_SORT_NONE) ?: [];
        $folders = [];
        $fileNames = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }

            $child = $linuxPath . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($child)) {
                $folders[] = [
                    'name' => $entry,
                    'path' => $this->childWindowsPath($normalized, $entry),
                ];
                continue;
            }

            if (!is_file($child)) {
                continue;
            }

            $extension = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if (!in_array($extension, ImageMimeEnum::libraryExtensions(), true)) {
                continue;
            }

            $fileNames[] = $entry;
        }

        usort($folders, static fn (array $left, array $right): int => strnatcasecmp($left['name'], $right['name']));
        usort($fileNames, static fn (string $left, string $right): int => strnatcasecmp($left, $right));

        $total = count($fileNames);
        $offset = max(0, ($page - 1) * $perPage);
        $pageNames = array_slice($fileNames, $offset, $perPage);
        $pagePaths = array_map(
            fn (string $name): string => $this->childWindowsPath($normalized, $name),
            $pageNames,
        );

        $known = Image::query()
            ->where('user_id', $userId)
            ->where('source', FileSourceEnum::WINDOWS->value)
            ->whereIn('external_url', $pagePaths)
            ->get()
            ->keyBy(static fn (Image $image): string => (string) $image->external_url);

        $files = [];
        foreach ($pageNames as $index => $name) {
            $absolute = $linuxPath . DIRECTORY_SEPARATOR . $name;
            $path = $pagePaths[$index];
            $image = $known->get($path);
            $thumbReady = $image !== null
                && $image->thumb_status === 'ready'
                && !$image->file_missing;

            $files[] = [
                'name' => $name,
                'path' => $path,
                'size' => (int) (@filesize($absolute) ?: 0),
                'mtime' => (int) (@filemtime($absolute) ?: 0),
                'in_gallery' => $image !== null,
                'file_missing' => $image !== null && (bool) $image->file_missing,
                'thumb_url' => $thumbReady ? $image->list_thumb_path : null,
            ];
        }

        return [
            'path' => $normalized,
            'name' => $this->basename($normalized),
            'breadcrumbs' => $this->breadcrumbs($normalized, $root),
            'folders' => $folders,
            'files' => $files,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    private function childWindowsPath(string $directory, string $name): string
    {
        return rtrim($directory, '\\') . '\\' . $name;
    }

    private function basename(string $windowsPath): string
    {
        $trimmed = rtrim($windowsPath, '\\');
        $parts = explode('\\', $trimmed);

        return (string) end($parts);
    }

    /**
     * @return list<array{name: string, path: string}>
     */
    private function breadcrumbs(string $windowsPath, string $rootFolder): array
    {
        $root = rtrim(WindowsPathHelper::normalizeWindows($rootFolder), '\\');
        $current = rtrim($windowsPath, '\\');
        $crumbs = [[
            'name' => $this->basename($root),
            'path' => $root,
        ]];

        if (strtolower($current) === strtolower($root)) {
            return $crumbs;
        }

        $relative = substr($current, strlen($root));
        $relative = ltrim(str_replace('/', '\\', $relative), '\\');
        $accumulator = $root;

        foreach (explode('\\', $relative) as $part) {
            if ($part === '') {
                continue;
            }

            $accumulator .= '\\' . $part;
            $crumbs[] = [
                'name' => $part,
                'path' => $accumulator,
            ];
        }

        return $crumbs;
    }
}
