<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;

class ParseTracksFromFolderTask extends ParentTask
{
    protected const array TRACK_EXTENSIONS = ['mp3'];

    public function run(string $path): array
    {
        return $this->parseTracks($path);
    }

    /**
     * Searches for tracks of the specified formats in folders and subfolders of the specified path
     *
     * @param string $path
     * @return array
     */
    private function parseTracks(string $path): array
    {
        $dirCanonical = realpath($path);

        static $items = [];

        if ($dirStream = opendir($dirCanonical)) {
            while (false !== ($fileName = readdir($dirStream))) {
                if ($fileName == "." || $fileName == "..") {
                    continue;
                }

                // ignore FLAC directory
                if ($fileName == 'FLAC') {
                    continue;
                }

                $dirItem = $dirCanonical . DIRECTORY_SEPARATOR . $fileName;

                if (is_dir($dirItem)) {
                    $this->parseTracks($dirItem);
                }

                $fileInfo = pathinfo($fileName);

                if (isset($fileInfo['extension']) && in_array($fileInfo['extension'], self::TRACK_EXTENSIONS)) {
                    $items[] = $dirItem;
                }
            }
        }

        return $items;
    }
}
