<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Tasks;

use App\Ship\Helpers\ImageUpload;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Http\UploadedFile;

class UploadMediaTask extends Task
{
    public function run(UploadedFile $file, string $albumID): string
    {
        $filename = $file->getClientOriginalName();

        return ImageUpload::make()
                          ->setDiskName('public')
                          ->setFolder("userfiles/gallery/{$albumID}")
                          ->setSourceName($filename)
                          ->upload($file);
    }
}
