<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ScanAudioFilesTask extends ParentTask
{
    public function run(string $linuxPath): array
    {
        $extensions = array_map('strtolower', (array) config('music_upload.extensions', ['mp3']));
        $skipDirectories = array_map('strtolower', (array) config('music_upload.skip_directories', ['flac']));
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($linuxPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $pathName = $file->getPathname();
            if ($this->isInsideSkippedDirectory($pathName, $skipDirectories)) {
                continue;
            }

            $extension = strtolower($file->getExtension());
            if (!in_array($extension, $extensions, true)) {
                continue;
            }

            $files[] = $pathName;
        }

        sort($files);

        return $files;
    }

    private function isInsideSkippedDirectory(string $path, array $skipDirectories): bool
    {
        $parts = preg_split('#[\\\\/]#', $path) ?: [];

        foreach ($parts as $part) {
            if (in_array(strtolower($part), $skipDirectories, true)) {
                return true;
            }
        }

        return false;
    }
}
