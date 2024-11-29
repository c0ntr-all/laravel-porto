<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Tasks;

use App\Ship\Helpers\ImageUpload;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Http\UploadedFile;

class SaveImageThumbTask extends Task
{
    public function run(UploadedFile $file, string $userId, string $albumID, $thumbType): string
    {
        $filename = $file->getFilename() . '_' . $thumbType . '_thumbnail';

        return ImageUpload::make()
                          ->setDiskName('public')
                          ->setFolder("userfiles/{$userId}/gallery/{$albumID}/thumbnails")
                          ->setSourceName($filename)
                          ->upload($file);

    }
}
