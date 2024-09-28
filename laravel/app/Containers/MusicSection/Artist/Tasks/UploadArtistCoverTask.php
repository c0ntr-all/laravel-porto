<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Tasks;

use App\Ship\Helpers\ImageUpload;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class UploadArtistCoverTask extends ParentTask
{
    public function run(UploadedFile $file, string $name, string $artistName): string
    {
        return ImageUpload::make()
                          ->setDiskName('public')
                          ->setFolder("music/artists/{$artistName}/covers")
                          ->setSourceName($name)
                          ->upload($file);
    }
}
