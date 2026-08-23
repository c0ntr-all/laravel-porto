<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;

class FindAlbumCoverTask extends ParentTask
{
    public function run(string $albumLinuxPath): ?string
    {
        foreach ((array) config('music_upload.cover_filenames', []) as $filename) {
            $coverPath = $albumLinuxPath . DIRECTORY_SEPARATOR . $filename;
            if (is_file($coverPath)) {
                return $coverPath;
            }
        }

        return null;
    }
}
