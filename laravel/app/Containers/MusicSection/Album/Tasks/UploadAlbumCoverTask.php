<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tasks;

use App\Ship\Helpers\ImageUpload;
use App\Ship\Helpers\StringHelper;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class UploadAlbumCoverTask extends ParentTask
{
    public function run(UploadedFile | File $file, int $artistId): string
    {
        $folder = "music/artists/{$artistId}/covers";
        $extension = $file->getExtension();
        $filename = StringHelper::generateFilename($extension);

        return ImageUpload::make()
                          ->setDiskName('public')
                          ->setFolder($folder)
                          ->setFilename($filename)
                          ->upload($file);
    }
}
