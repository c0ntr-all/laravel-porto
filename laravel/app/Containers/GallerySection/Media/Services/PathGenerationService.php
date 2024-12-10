<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Services;

use Illuminate\Support\Facades\Storage;

class PathGenerationService
{
    public function getAlbumFolderPath(string $userId, string $albumId): string
    {
        return "userfiles/{$userId}/gallery/{$albumId}";
    }

    public function preparePathsForThumbnail(string $fullPath, string $thumbType, string $albumPath): array
    {
        $info = pathinfo($fullPath);

        $thumbName = sprintf('%s_%s_thumbnail.%s', $info['filename'], $thumbType, $info['extension']);
        $thumbsFolderPath = "{$albumPath}/thumbnails";
        $thumbFullPath = Storage::disk('public')->path("{$thumbsFolderPath}/{$thumbName}");

        return [
            'thumb_path' => "{$thumbsFolderPath}/{$thumbName}",
            'thumbs_folder_path' => $thumbsFolderPath,
            'thumb_full_path' => $thumbFullPath,
        ];
    }

    public function prepareFolder(string $folder): void
    {
        if (!Storage::exists($folder)) {
            Storage::makeDirectory($folder);
        }
    }
}
