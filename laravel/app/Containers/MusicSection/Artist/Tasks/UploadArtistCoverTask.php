<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Tasks;

use App\Ship\Helpers\ImageUpload;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;

class UploadArtistCoverTask extends ParentTask
{
    public function run(UploadedFile | File $file, string $name, string $artistId): string
    {
        return ImageUpload::make()
                          ->setDiskName('public')
                          ->setFolder("music/artists/{$artistId}/images")
                          ->setSourceName($name)
                          ->upload($file);
    }
}
