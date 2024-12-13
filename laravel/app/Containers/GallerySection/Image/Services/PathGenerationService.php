<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Services;

use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\ArrayShape;

class PathGenerationService
{
    public function getAlbumFolderPath(string $userId, string $albumId): string
    {
        return "userfiles/{$userId}/gallery/{$albumId}";
    }

    #[ArrayShape(['thumb_path' => 'string', 'thumbs_folder_path' => 'string', 'thumb_full_path' => 'string'])]
    public function preparePathsForThumbnail(string $albumPath, string $basename, string $thumbType): array
    {
        $info = pathinfo($basename);

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
