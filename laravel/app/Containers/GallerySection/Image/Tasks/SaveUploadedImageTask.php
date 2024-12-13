<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Ship\Helpers\ImageUpload;
use App\Ship\Helpers\StringHelper;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Http\UploadedFile;
use Random\RandomException;

class SaveUploadedImageTask extends Task
{
    /**
     * @throws RandomException
     */
    public function run(UploadedFile $file, string $folder): string
    {
        $extension = $file->getClientOriginalExtension();

        $filename = StringHelper::generateFilename($extension);

        return ImageUpload::make()
                          ->setDiskName('public')
                          ->setFolder($folder)
                          ->setFilename($filename)
                          ->upload($file);
    }
}
