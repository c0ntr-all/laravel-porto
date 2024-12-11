<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Tasks;

use App\Ship\Helpers\ImageUpload;
use App\Ship\Helpers\StringHelper;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;

class UploadArtistCoverTask extends ParentTask
{
    public function run(UploadedFile | File $file, string $artistId): string
    {
        $folder = "music/artists/{$artistId}/images";
        $filename = StringHelper::generateFilename($file->getExtension());

        return ImageUpload::make()
                          ->setDiskName('public')
                          ->setFolder($folder)
                          ->setFilename($filename)
                          ->upload($file);
    }
}
