<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\Tasks;

use App\Ship\Helpers\ImageUpload;
use App\Ship\Helpers\StringHelper;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class UploadFranchiseImageTask extends ParentTask
{
    public function run(UploadedFile|File $file, string $franchiseId): string
    {
        $folder = "movies/franchises/{$franchiseId}/images";
        $extension = $file->getExtension() ?: $file->guessExtension() ?: 'jpg';
        $filename = StringHelper::generateFilename($extension);

        return ImageUpload::make()
            ->setDiskName('public')
            ->setFolder($folder)
            ->setFilename($filename)
            ->upload($file);
    }
}
