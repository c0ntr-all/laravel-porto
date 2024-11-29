<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Tasks;

use App\Ship\Helpers\ImageUpload;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Http\UploadedFile;

class UploadMediaTask extends Task
{
    public function run(UploadedFile $file, int $userId, string $albumID): string
    {
        $filename = $file->getClientOriginalName();
        $filenameWithoutExtension = $this->deleteExtension($filename);

        return ImageUpload::make()
                          ->setDiskName('public')
                          ->setFolder("userfiles/{$userId}/gallery/{$albumID}")
                          ->setSourceName($filenameWithoutExtension)
                          ->upload($file);
    }

    private function deleteExtension(string $filename): string
    {
        $filenameParts = explode('.', $filename);

        return $filenameParts[0];
    }
}
