<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\Tasks;

use App\Ship\Helpers\ImageUpload;
use App\Ship\Helpers\StringHelper;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class UploadPlaylistCoverTask extends ParentTask
{
    public function run(UploadedFile|File $file, string|int $playlistId): string
    {
        $folder = "music/playlists/{$playlistId}/images";
        $filename = StringHelper::generateFilename($file->getExtension());

        return ImageUpload::make()
                          ->setDiskName('public')
                          ->setFolder($folder)
                          ->setFilename($filename)
                          ->upload($file);
    }
}
