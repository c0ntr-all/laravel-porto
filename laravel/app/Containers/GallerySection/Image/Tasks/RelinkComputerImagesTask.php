<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Image\Enums\ImageMimeEnum;
use App\Containers\GallerySection\Image\Jobs\GenerateGalleryImageThumbJob;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Helpers\WindowsPathHelper;
use App\Ship\Parents\Tasks\Task as ParentTask;

class RelinkComputerImagesTask extends ParentTask
{
    /**
     * Matches a missing Windows image to a file in one folder by size and mtime.
     * File bytes are not hashed. Ambiguous matches are left alone.
     *
     * @return array{relinked: int, still_missing: int}
     */
    public function run(string $windowsPath, int $userId): array
    {
        $disk = (string) config('image.windows.disk', 'windows_f');
        $root = (string) config('image.windows.root_folder', config('app.windows_images_root_folder'));
        $normalized = WindowsPathHelper::normalizeWindows($windowsPath);
        $linuxPath = WindowsPathHelper::assertReadableDirectory($normalized, $disk, $root);

        $candidates = $this->filesInFolder($linuxPath, $normalized);
        $images = Image::query()
            ->where('user_id', $userId)
            ->where('source', FileSourceEnum::WINDOWS->value)
            ->get();

        $missing = [];
        foreach ($images as $image) {
            if ($this->pathStillReadable($image, $disk, $root)) {
                if ($image->file_missing) {
                    $image->forceFill(['file_missing' => false])->save();
                }
                continue;
            }

            if (!$image->file_missing) {
                $image->forceFill(['file_missing' => true])->save();
            }
            $missing[] = $image;
        }

        $relinked = 0;
        foreach ($missing as $image) {
            if ($image->file_size === null || $image->file_mtime === null) {
                continue;
            }

            $key = $image->file_size . ':' . $image->file_mtime;
            $matches = $candidates[$key] ?? [];
            $chosen = $this->choose($matches, (string) $image->external_url);
            if ($chosen === null) {
                continue;
            }

            $regenerateThumb = $image->thumb_status === 'failed';
            $image->forceFill([
                'external_url' => $chosen,
                'file_missing' => false,
                'thumb_status' => $regenerateThumb ? 'pending' : $image->thumb_status,
            ])->save();

            if ($regenerateThumb) {
                GenerateGalleryImageThumbJob::dispatch($image->id)->afterCommit();
            }

            $relinked++;
        }

        return [
            'relinked' => $relinked,
            'still_missing' => count($missing) - $relinked,
        ];
    }

    /**
     * @param list<array{path: string, name: string}> $matches
     */
    private function choose(array $matches, string $previousPath): ?string
    {
        if (count($matches) === 1) {
            return $matches[0]['path'];
        }

        $previousName = strtolower(basename(str_replace('\\', '/', $previousPath)));
        $named = array_values(array_filter(
            $matches,
            static fn (array $match): bool => strtolower($match['name']) === $previousName,
        ));

        return count($named) === 1 ? $named[0]['path'] : null;
    }

    /**
     * @return array<string, list<array{path: string, name: string}>>
     */
    private function filesInFolder(string $linuxPath, string $windowsDirectory): array
    {
        $entries = scandir($linuxPath, SCANDIR_SORT_NONE) ?: [];
        $grouped = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }

            $absolute = $linuxPath . DIRECTORY_SEPARATOR . $entry;
            if (!is_file($absolute)) {
                continue;
            }

            $extension = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if (!in_array($extension, ImageMimeEnum::libraryExtensions(), true)) {
                continue;
            }

            $size = @filesize($absolute);
            $mtime = @filemtime($absolute);
            if ($size === false || $mtime === false) {
                continue;
            }

            $key = $size . ':' . $mtime;
            $grouped[$key][] = [
                'name' => $entry,
                'path' => rtrim($windowsDirectory, '\\') . '\\' . $entry,
            ];
        }

        return $grouped;
    }

    private function pathStillReadable(Image $image, string $disk, string $root): bool
    {
        if ($image->external_url === null || trim($image->external_url) === '') {
            return false;
        }

        try {
            WindowsPathHelper::assertReadableFile($image->external_url, $disk, $root);

            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
