<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Artist\Data\Repositories\ArtistRepository;
use App\Containers\MusicSection\Upload\Data\DTO\LibraryFolderNode;
use App\Containers\MusicSection\Upload\Helpers\PathHelper;
use App\Ship\Parents\Tasks\Task as ParentTask;

class ListLibraryFoldersTask extends ParentTask
{
    public function __construct(
        private readonly ArtistRepository $artistRepository,
        private readonly AlbumRepository $albumRepository,
    ) {
    }

    /**
     * @return list<LibraryFolderNode>
     */
    public function run(string $windowsPath): array
    {
        $linuxPath = PathHelper::resolveLibraryDirectory($windowsPath);
        $skipDirectories = array_map('strtolower', (array) config('music_upload.skip_directories', ['flac']));
        $entries = scandir($linuxPath) ?: [];
        $folders = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }

            if (in_array(strtolower($entry), $skipDirectories, true)) {
                continue;
            }

            $childLinux = $linuxPath . DIRECTORY_SEPARATOR . $entry;
            if (!is_dir($childLinux)) {
                continue;
            }

            $childWindows = PathHelper::toWindows($childLinux);
            $folders[] = [
                'name' => $entry,
                'path' => $childWindows,
                'linux' => $childLinux,
            ];
        }

        usort($folders, static fn (array $left, array $right): int => strnatcasecmp($left['name'], $right['name']));

        $paths = array_column($folders, 'path');
        $uploaded = array_flip([
            ...$this->artistRepository->existingPaths($paths),
            ...$this->albumRepository->existingPaths($paths),
        ]);

        return array_map(
            fn (array $folder): LibraryFolderNode => LibraryFolderNode::from([
                'id' => $folder['path'],
                'name' => $folder['name'],
                'path' => $folder['path'],
                'has_children' => $this->hasVisibleSubdirectories($folder['linux'], $skipDirectories),
                'uploaded' => isset($uploaded[$folder['path']]),
            ]),
            $folders,
        );
    }

    /**
     * @param list<string> $skipDirectories
     */
    private function hasVisibleSubdirectories(string $linuxPath, array $skipDirectories): bool
    {
        $entries = @scandir($linuxPath, SCANDIR_SORT_NONE);
        if ($entries === false) {
            return false;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }

            if (in_array(strtolower($entry), $skipDirectories, true)) {
                continue;
            }

            if (is_dir($linuxPath . DIRECTORY_SEPARATOR . $entry)) {
                return true;
            }
        }

        return false;
    }
}
