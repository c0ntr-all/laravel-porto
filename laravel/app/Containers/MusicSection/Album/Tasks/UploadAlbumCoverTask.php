<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tasks;

use App\Ship\Helpers\ImageUpload;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class UploadAlbumCoverTask extends ParentTask
{
    public function run(UploadedFile | File $file, string $name, int $artistId): string
    {
        return ImageUpload::make()
                          ->setDiskName('public')
                          ->setFolder("music/artists/{$artistId}/covers")
                          ->setSourceName($name)
                          ->upload($file);
    }
}
