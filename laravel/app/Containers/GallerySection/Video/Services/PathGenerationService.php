<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\ArrayShape;

class PathGenerationService
{
    public function diskName(): string
    {
        return (string) config('video.disk', 'public');
    }

    public function getAlbumFolderPath(string $userId, string $albumId): string
    {
        return "userfiles/{$userId}/videos/{$albumId}";
    }

    public function getOriginalRelativePath(string $userId, string $albumId, string $fileId, string $extension): string
    {
        return "{$this->getAlbumFolderPath($userId, $albumId)}/{$fileId}.{$extension}";
    }

    public function getListThumbRelativePath(string $userId, string $albumId, string $fileId): string
    {
        return "{$this->getAlbumFolderPath($userId, $albumId)}/thumbnails/{$fileId}_list_thumbnail.jpg";
    }

    #[ArrayShape(['thumb_path' => 'string', 'thumbs_folder_path' => 'string', 'thumb_full_path' => 'string'])]
    public function preparePathsForThumbnail(string $albumPath, string $fileId): array
    {
        $thumbName = "{$fileId}_list_thumbnail.jpg";
        $thumbsFolderPath = "{$albumPath}/thumbnails";
        $disk = Storage::disk($this->diskName());

        return [
            'thumb_path' => "{$thumbsFolderPath}/{$thumbName}",
            'thumbs_folder_path' => $thumbsFolderPath,
            'thumb_full_path' => $disk->path("{$thumbsFolderPath}/{$thumbName}"),
        ];
    }

    public function prepareFolder(string $folder): void
    {
        $disk = Storage::disk($this->diskName());
        $absolutePath = $disk->path($folder);

        if (!File::exists($absolutePath)) {
            File::makeDirectory($absolutePath, 0755, true);
        }
    }
}
