<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tasks;

use App\Ship\Helpers\ImageUpload;
use App\Ship\Helpers\StringHelper;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class UploadTrackCoverTask extends ParentTask
{
    public function run(UploadedFile | File $file, int $trackId): string
    {
        $folder = "music/tracks/{$trackId}/images";
        $filename = StringHelper::generateFilename($file->getExtension());

        return ImageUpload::make()
                          ->setDiskName('public')
                          ->setFolder($folder)
                          ->setFilename($filename)
                          ->upload($file);
    }
}
