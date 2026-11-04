<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use DateTimeImmutable;

class ReadImageFileFactsTask extends ParentTask
{
    /**
     * Header-level facts only. getimagesize and EXIF do not decode the bitmap.
     *
     * @return array{width: int, height: int, file_size: int|null, file_mtime: int|null, taken_at: string|null}
     */
    public function run(string $absolutePath): array
    {
        $size = @filesize($absolutePath);
        $mtime = @filemtime($absolutePath);
        $info = @getimagesize($absolutePath);

        return [
            'width' => is_array($info) ? (int) $info[0] : 0,
            'height' => is_array($info) ? (int) $info[1] : 0,
            'file_size' => $size === false ? null : $size,
            'file_mtime' => $mtime === false ? null : $mtime,
            'taken_at' => $this->takenAt($absolutePath),
        ];
    }

    private function takenAt(string $absolutePath): ?string
    {
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'jfif'], true) || !function_exists('exif_read_data')) {
            return null;
        }

        $exif = @exif_read_data($absolutePath, null, true);
        if (!is_array($exif)) {
            return null;
        }

        $raw = $exif['EXIF']['DateTimeOriginal'] ?? $exif['IFD0']['DateTimeOriginal'] ?? null;
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('Y:m:d H:i:s', $raw);

        return $parsed === false ? null : $parsed->format('Y-m-d H:i:s');
    }
}
